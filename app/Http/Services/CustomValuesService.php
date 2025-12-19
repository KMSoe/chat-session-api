<?php
namespace App\Http\Services;

use App\Enums\AttributeType;
use App\Helpers\General;
use App\Models\Attribute;
use App\Models\CustomValue;
use App\Models\Module;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\CRM\App\Models\ItemCustomValue;
use Modules\Password\App\Helpers\CryptoHelper;
use Modules\Storage\App\Classes\ObjectStorage;
use Modules\Storage\App\Models\File;

class CustomValuesService
{
    public function save(array $data, int $targetId, string $moduleName): void
    {
        $module = Module::whereRaw('BINARY `name` = ?', [$moduleName])->first();
        if ($module) {
            $attributes = $module->attributes;

            foreach ($attributes as $attribute) {
                if (array_key_exists($attribute->name, $data)) {
                    $value = $data[$attribute->name];

                    $processedValue = $this->processValueForStorage($value, $attribute->type);

                    if ($this->shouldEncrypt($attribute->type)) {
                        $processedValue = $this->encryptValue($processedValue, $attribute->type);
                    }

                    $value = CustomValue::updateOrCreate(
                        [
                            'attribute_id' => $attribute->id,
                            'target_id'    => $targetId,
                        ],
                        [
                            'target_value' => $processedValue,
                            'is_encrypted' => $this->shouldEncrypt($attribute->type),
                        ]
                    );
                }
            }
        }
    }

    public function saveItemCustomValuesWithAttributes(array $data, int $targetId, $attributes): void
    {
        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute->name, $data)) {
                $value = $data[$attribute->name];

                $processedValue = $this->processValueForStorage($value, $attribute->type);

                if ($this->shouldEncrypt($attribute->type)) {
                    $processedValue = $this->encryptValue($processedValue, $attribute->type);
                }

                $value = ItemCustomValue::updateOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'target_id'    => $targetId,
                    ],
                    [
                        'target_value' => $processedValue,
                        'is_encrypted' => $this->shouldEncrypt($attribute->type),
                    ]
                );
            }
        }

    }

    public function get(int $targetId, string $moduleName): array
    {
        $module = Module::whereRaw('BINARY `name` = ?', [$moduleName])->first();

        if (! $module) {
            return [];
        }

        return $module->attributes()
            ->with(['customValues' => function ($q) use ($targetId) {
                $q->where('target_id', $targetId);
            }])
            ->get()
            ->mapWithKeys(function ($attribute) {
                $customValue = $attribute->customValues->first();
                $rawValue    = $customValue->target_value ?? null;

                // Decrypt if encrypted
                if ($customValue && $customValue->is_encrypted && $rawValue) {
                    $rawValue = $this->decryptValue($rawValue, $attribute->type);
                }

                $castedValue = $this->castValue($rawValue, $attribute->type);

                return [$attribute->name => $castedValue];
            })->toArray();
    }

    public function getDecryptedValues(int $targetId, string $moduleName): array
    {
        $module = Module::whereRaw('BINARY `name` = ?', [$moduleName])->first();

        if (! $module) {
            return [];
        }

        return $module->attributes()
            ->with(['customValues' => function ($q) use ($targetId) {
                $q->where('target_id', $targetId);
            }])
            ->get()
            ->mapWithKeys(function ($attribute) {
                $customValue = $attribute->customValues->first();
                $rawValue    = $customValue->target_value ?? null;

                $type = is_string($attribute->type)
                    ? AttributeType::tryFrom($attribute->type)
                    : $attribute->type;

                if ($customValue && $customValue->is_encrypted && $rawValue) {
                    if ($type === AttributeType::ENCRYPTEDHTML) {
                        $rawValue = $this->getOriginalHTMLInfo($rawValue);
                    } else {
                        $rawValue = $this->decryptValue($rawValue, $type);
                    }
                }

                $castedValue = $this->castValue($rawValue, $type ?? $attribute->type);

                return [$attribute->name => $castedValue];
            })
            ->toArray();
    }

    public function getValidationRules(string $moduleName): array
    {
        $module = Module::whereRaw('BINARY `name` = ?', [$moduleName])->first();
        $rules  = [];

        if (! $module) {
            return $rules;
        }

        $attributes = $module->attributes()->where('status', 1)->get();

        foreach ($attributes as $attribute) {
            $ruleSet = [];

            if ($attribute->is_required) {
                $ruleSet[] = 'required';
            } else {
                $ruleSet[] = 'nullable';
            }

            $type = is_string($attribute->type) ? AttributeType::tryFrom($attribute->type) : $attribute->type;

            switch ($type) {
                case AttributeType::NUMBER:
                    $ruleSet[] = 'numeric';
                    break;
                case AttributeType::RADIO:
                case AttributeType::BOOLEAN:
                    // case AttributeType::CHECKBOX:
                    $ruleSet[] = 'boolean';
                    break;
                case AttributeType::DATE:
                    $ruleSet[] = 'date';
                    break;
                // case AttributeType::DATETIME:
                //     $ruleSet[] = 'date_format:Y-m-d H:i:s';
                //     break;
                // case AttributeType::JSON:
                //     $ruleSet[] = 'json';
                //     break;
                case AttributeType::SELECT:
                    if ($attribute->options && is_array($attribute->options)) {
                        $ruleSet[] = Rule::in(array_keys($attribute->options));
                    }
                    break;
                case AttributeType::PASSWORD:
                    $ruleSet[] = 'string';
                    $ruleSet[] = 'min:6';
                    break;
                case AttributeType::FILES:
                    $ruleSet[] = 'array';
                    $ruleSet[] = 'exists:files,id';
                    break;
                case AttributeType::ENCRYPTEDFILE:
                    $ruleSet[] = 'integer';
                    $ruleSet[] = 'exists:files,id';
                    break;
                case AttributeType::ENCRYPTEDHTML:
                    $ruleSet[] = 'string';
                    break;
                default:
                    $ruleSet[] = 'string';
                    break;
            }

            $rules[$attribute->name] = $ruleSet;
        }

        return $rules;
    }

    public function getValidationRulesByAttributeIds(array $ids): array
    {

        $rules = [];

        $attributes = Attribute::whereIn('id', $ids)->get();

        foreach ($attributes as $attribute) {
            $ruleSet = [];

            if ($attribute->is_required) {
                $ruleSet[] = 'required';
            } else {
                $ruleSet[] = 'nullable';
            }

            $type = is_string($attribute->type) ? AttributeType::tryFrom($attribute->type) : $attribute->type;

            switch ($type) {
                case AttributeType::NUMBER:
                    $ruleSet[] = 'numeric';
                    break;
                case AttributeType::RADIO:
                case AttributeType::BOOLEAN:
                    // case AttributeType::CHECKBOX:
                    $ruleSet[] = 'boolean';
                    break;
                case AttributeType::DATE:
                    $ruleSet[] = 'date';
                    break;
                // case AttributeType::DATETIME:
                //     $ruleSet[] = 'date_format:Y-m-d H:i:s';
                //     break;
                // case AttributeType::JSON:
                //     $ruleSet[] = 'json';
                //     break;
                case AttributeType::SELECT:
                    if ($attribute->options && is_array($attribute->options)) {
                        $ruleSet[] = Rule::in(array_keys($attribute->options));
                    }
                    break;
                case AttributeType::PASSWORD:
                    $ruleSet[] = 'string';
                    $ruleSet[] = 'min:6';
                    break;
                case AttributeType::FILES:
                    $ruleSet[] = 'array';
                    $ruleSet[] = 'exists:files,id';
                    break;
                case AttributeType::ENCRYPTEDFILE:
                    $ruleSet[] = 'integer';
                    $ruleSet[] = 'exists:files,id';
                    break;
                case AttributeType::ENCRYPTEDHTML:
                    $ruleSet[] = 'string';
                    break;
                default:
                    $ruleSet[] = 'string';
                    break;
            }

            $rules[$attribute->name] = $ruleSet;
        }

        return $rules;
    }

    public function validate(string $moduleName, array $data)
    {
        $rules = $this->getValidationRules($moduleName);
        return Validator::make($data, $rules);
    }

    public function castValue(mixed $value, AttributeType | string $type): mixed
    {
        if (is_string($type)) {
            $type = AttributeType::tryFrom($type);
        }

        return match ($type) {
            AttributeType::NUMBER        => is_numeric($value) ? +$value : null,
            AttributeType::BOOLEAN       => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            // AttributeType::CHECKBOX => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            AttributeType::DATE          => $value ? date('Y-m-d', strtotime($value)) : null,
            // AttributeType::DATETIME      => $value ? date('Y-m-d H:i:s', strtotime($value)) : null,
            // AttributeType::JSON     => is_array($value) || is_object($value) ? json_encode($value) : $value,
            AttributeType::PASSWORD      => $value,
            AttributeType::FILE          => $this->getFilePaths([$value]),
            AttributeType::FILES         => $this->getFilePaths($value),
            AttributeType::ENCRYPTEDHTML => $value,
            AttributeType::ENCRYPTEDFILE => $this->getDecryptedFilePath($value),
            default                      => $value,
        };
    }

    /**
     * Check if a field type should be encrypted
     */
    protected function shouldEncrypt(AttributeType | string $type): bool
    {
        if (is_string($type)) {
            $type = AttributeType::tryFrom($type);
        }

        return match ($type) {
            AttributeType::PASSWORD      => true,
            AttributeType::ENCRYPTEDHTML => true,
            AttributeType::ENCRYPTEDFILE => true,
            default                      => false,
        };
    }

    /**
     * Process value for storage based on type
     */
    protected function processValueForStorage(mixed $value, AttributeType | string $type): mixed
    {
        if (is_string($type)) {
            $type = AttributeType::tryFrom($type);
        }

        return match ($type) {
            AttributeType::FILES         => is_array($value) ? json_encode($value) : $value,
            AttributeType::ENCRYPTEDFILE => $this->processEncryptedFile($value),
            default                      => $value,
        };
    }

    /**
     * Get file paths from file IDs
     */
    protected function getFilePaths(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        $fileIds = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($fileIds)) {
            return [];
        }

        return File::whereIn('id', $fileIds)
            ->get()
            ->map(function ($file) {
                return [
                    'id'          => $file->id,
                    'name'        => $file->name,
                    'basename'    => $file->basename,
                    'description' => $file->description,
                    'path'        => $file->path,
                    'type'        => $file->type,
                    'extension'   => $file->extension,
                    'size'        => $file->size,
                    'file_hash'   => $file->file_hash,
                    'folder_id'   => $file->folder_id,
                ];
            })
            ->toArray();
    }

    /**
     * Encrypt a value
     */
    protected function encryptValue(mixed $value, $type): string
    {
        if ($type === AttributeType::ENCRYPTEDFILE || (is_string($type) && $type === AttributeType::ENCRYPTEDFILE->value)) {
            return $value;
        }

        if (empty($value)) {
            return '';
        }

        // Use Laravel Crypt for ENCRYPTEDHTML and large data
        if ($type === AttributeType::ENCRYPTEDHTML || (is_string($type) && $type === AttributeType::ENCRYPTEDHTML->value)) {
            return Crypt::encryptString($value);
        }

        // For passwords or small secrets, use your asymmetric logic
        $user       = auth()->user();
        $cryption   = CryptoHelper::getKeyPairByUser($user->id);
        $plain_text = CryptoHelper::decryptByUserPrivateKey($cryption->private_key, $value);

        $result = General::encryptData($plain_text);

        return $result;
    }

    /**
     * Process encrypted file - decrypt original and create new decrypted file
     */
    protected function processEncryptedFile(mixed $fileId): int
    {
        if (empty($fileId)) {
            return 0;
        }

        $originalFile = File::find($fileId);
        if (! $originalFile) {
            return 0;
        }

        try {
            $filePath = $originalFile->path;

            if (! Storage::disk('s3')->exists($filePath)) {
                return 0;
            }

            $originalContent = app(ObjectStorage::class)->getFile($filePath);

            // Decrypt file content using user's private key
            $encryptedContent = Crypt::encryptString($originalContent);

            // Save encrypted file
            $encryptedFileName = 'encrypted_' . time() . '_' . $originalFile->name;
            $encryptedPath     = 'encrypted_files/' . $encryptedFileName;
            Storage::disk('s3')->put($encryptedPath, $encryptedContent);

            $encryptedFile = File::create([
                'folder_id'   => $originalFile->folder_id,
                'name'        => $encryptedFileName,
                'basename'    => 'encrypted_' . $originalFile->basename,
                'description' => 'Encrypted version of: ' . $originalFile->description,
                'path'        => $encryptedPath,
                'type'        => $originalFile->type,
                'extension'   => $originalFile->extension,
                'size'        => strlen($encryptedContent),
                'file_hash'   => hash('sha256', $encryptedContent),
                'uploaded_by' => auth()->id(),
            ]);

            return $encryptedFile->id;

        } catch (\Exception $e) {
            Log::warning("Failed to process encrypted file: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get original file info from decrypted file ID
     */
    public function getOriginalFileInfo(mixed $decryptedFileId): array
    {
        if (empty($decryptedFileId)) {
            return [];
        }

        $decryptedFile = File::find($decryptedFileId);
        if (! $decryptedFile) {
            return [];
        }

        $originalFileName = str_replace('encrypted_', '', $decryptedFile->basename);
        $originalFile     = File::where('basename', $originalFileName)
            ->where('id', '!=', $decryptedFileId)
            ->first();

        if ($originalFile) {
            return [
                'id'                => $originalFile->id,
                'name'              => $originalFile->name,
                'basename'          => $originalFile->basename,
                'description'       => $originalFile->description,
                'path'              => $originalFile->path,
                'type'              => $originalFile->type,
                'extension'         => $originalFile->extension,
                'size'              => $originalFile->size,
                'file_hash'         => $originalFile->file_hash,
                'folder_id'         => $originalFile->folder_id,
                'decrypted_file_id' => $decryptedFileId,
            ];
        }

        // If original not found, return decrypted file info
        return [
            'id'          => $decryptedFile->id,
            'name'        => $decryptedFile->name,
            'basename'    => $decryptedFile->basename,
            'description' => $decryptedFile->description,
            'path'        => $decryptedFile->path,
            'type'        => $decryptedFile->type,
            'extension'   => $decryptedFile->extension,
            'size'        => $decryptedFile->size,
            'file_hash'   => $decryptedFile->file_hash,
            'folder_id'   => $decryptedFile->folder_id,
        ];
    }

    /**
     * Decrypt a value
     */
    public function decryptValue(string $encryptedValue, $type): ?string
    {
        if ($type === AttributeType::ENCRYPTEDFILE || (is_string($type) && $type === AttributeType::ENCRYPTEDFILE->value)) {
            return $encryptedValue;
        }

        if ($type === AttributeType::ENCRYPTEDHTML || (is_string($type) && $type === AttributeType::ENCRYPTEDHTML->value)) {
            return $encryptedValue;
        }

        if (empty($encryptedValue)) {
            return null;
        }

        // For password or small secrets
        try {
            $user      = auth()->user();
            $decrypted = General::decryptData($encryptedValue);
            $cryption  = CryptoHelper::getKeyPairByUser($user->id);
            $result    = CryptoHelper::encryptDataWithUserPublicKey($cryption->public_key, $decrypted);
            return $result;
        } catch (\Exception $e) {
            Log::warning("Failed to decrypt custom value: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get decrypted file path by decrypted file ID
     */
    protected function getDecryptedFilePath($decryptedFileId): ?string
    {
        if (empty($decryptedFileId)) {
            return null;
        }

        $decryptedFile = File::find($decryptedFileId);
        if (! $decryptedFile) {
            return null;
        }

        return $decryptedFile;
    }

    public function getOriginalHTMLInfo($encryptedValue)
    {
        return Crypt::decryptString($encryptedValue);
    }

    public function checkDuplicateName(string $name, array $moduleIds, ?int $ignoreId = null): void
    {
        if(!$ignoreId) {
            $existingAttributes = Attribute::whereRaw('BINARY `name` = ?', [$name])
            ->whereHas('modules', function ($query) use ($moduleIds) {
                $query->whereIn('module_id', $moduleIds);
            })
            ->get();

            if ($existingAttributes->isNotEmpty()) {
                throw new \Exception("An attribute with the name '{$name}' already exists in one of the specified modules.");
            }
        } else {
            $existingAttributes = Attribute::whereRaw('BINARY `name` = ?', [$name])
            ->where('id', '!=', $ignoreId)
            ->whereHas('modules', function ($query) use ($moduleIds) {
                $query->whereIn('module_id', $moduleIds);
            })
            ->get();

            if ($existingAttributes->isNotEmpty()) {
                throw new \Exception("An attribute with the name '{$name}' already exists in one of the specified modules.");
            }
        }
    }
}

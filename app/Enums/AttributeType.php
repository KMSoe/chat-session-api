<?php

namespace App\Enums;

enum AttributeType: string
{
    case TEXTBOX   = 'textbox';
    case TEXTAREA  = 'textarea';
    case NUMBER    = 'number';
    case SELECT    = 'select';
    case RADIO     = 'radio';
    // case CHECKBOX  = 'checkbox';
    case BOOLEAN   = 'boolean';
    case DATE      = 'date';
    // case DATETIME  = 'datetime';
    case EMAIL     = 'email';
    case FILE      = 'file';
    // case JSON      = 'json';
    case PASSWORD  = 'password';
    case FILES     = 'files';
    case ENCRYPTEDHTML = 'encrypted_html';
    case ENCRYPTEDFILE = 'encrypted_file';

    public function label(): string
    {
        return match ($this) {
            self::TEXTBOX   => 'Text Box',
            self::TEXTAREA  => 'Text Area',
            self::NUMBER    => 'Number',
            self::SELECT    => 'Select Dropdown',
            self::RADIO     => 'Radio Buttons',
            // self::CHECKBOX  => 'Checkbox',
            self::BOOLEAN   => 'Boolean Toggle',
            self::DATE      => 'Date',
            // self::DATETIME  => 'Date & Time',
            self::EMAIL     => 'Email',
            self::FILE      => 'File Upload',
            // self::JSON      => 'JSON Object',
            self::PASSWORD  => 'Encrypted Text',
            self::FILES     => 'Multiple Files',
            self::ENCRYPTEDHTML => 'Encrypted HTML',
            self::ENCRYPTEDFILE => 'Encrypted File',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
<?php

namespace Modules\CRM\App\Enum;

enum QuotationStatus: string {
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case INVOICED = 'invoiced';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';
    
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::SENT => 'Sent',
            self::ACCEPTED => 'Accepted',
            self::INVOICED => 'Invoiced',
            self::DECLINED => 'Declined',
            self::EXPIRED => 'Expired',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

     public static function labels(): array
    {
        return array_combine(
            self::values(),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }

    public static function toArray(): array
    {
        return array_map(
            fn (self $enum) => [
                'value' => $enum->value,
                'label' => $enum->label(),
            ],
            self::cases()
        );
    }
}

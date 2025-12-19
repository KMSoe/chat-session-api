<?php

namespace Modules\CRM\App\Enum;

enum InvoiceStatus: string {
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SENT = 'sent';
    case UNPAID = 'unpaid';
    case PARTIALLY_PAID = 'partially_paid';
    case OVERDUE = 'overdue';
    case PAID = 'paid';
    case VOID = 'void';
    case WRITE_OFF = 'write_off';
    
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::SENT => 'Sent',
            self::UNPAID => 'Unpaid',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::OVERDUE => 'Overdue',
            self::PAID => 'Paid',
            self::VOID => 'Void',
            self::WRITE_OFF => 'Write Off',
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

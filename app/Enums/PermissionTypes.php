<?php
namespace App\Enums;

enum PermissionTypes: string {
    case APPROVE  = 'approve';
    case ASSIGN   = 'assign';
    case CHANGE   = 'change';
    case CREATE   = 'create';
    case DELETE   = 'delete';
    case DETAIL   = 'detail';
    case DISABLE  = 'disable';
    case DOWNLOAD = 'download';
    case EDIT     = 'edit';
    case ENABLE   = 'enable';
    case EXPORT   = 'export';
    case GENERATE = 'generate';
    case IMPORT   = 'import';
    case PUBLISH  = 'publish';
    case REJECT   = 'reject';
    case RESET    = 'reset';
    case UPLOAD   = 'upload';
    case VIEW     = 'view';
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

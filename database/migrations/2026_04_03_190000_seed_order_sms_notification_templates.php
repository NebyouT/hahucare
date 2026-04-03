<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Constant\Models\Constant;
use Modules\NotificationTemplate\Models\NotificationTemplate;

return new class extends Migration
{
    public function up(): void
    {
        // Add order notification type constants
        $orderTypes = [
            ['type' => 'notification_type', 'value' => 'order_confirmed', 'name' => 'Order Confirmed'],
            ['type' => 'notification_type', 'value' => 'order_processing', 'name' => 'Order Processing'],
            ['type' => 'notification_type', 'value' => 'order_out_for_delivery', 'name' => 'Order Out For Delivery'],
            ['type' => 'notification_type', 'value' => 'order_delivered', 'name' => 'Order Delivered'],
            ['type' => 'notification_type', 'value' => 'order_cancelled', 'name' => 'Order Cancelled'],
            // Order param buttons
            ['type' => 'notification_param_button', 'value' => 'order_code', 'name' => 'Order Code'],
            ['type' => 'notification_param_button', 'value' => 'order_date', 'name' => 'Order Date'],
            ['type' => 'notification_param_button', 'value' => 'order_time', 'name' => 'Order Time'],
        ];

        foreach ($orderTypes as $value) {
            Constant::updateOrCreate(
                ['type' => $value['type'], 'value' => $value['value']],
                $value
            );
        }

        // --- Order Confirmed ---
        $template = NotificationTemplate::updateOrCreate(
            ['type' => 'order_confirmed'],
            [
                'name' => 'order_confirmed',
                'label' => 'Order Confirmed',
                'status' => 1,
                'to' => '["user","admin"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '1', 'IS_WHATSAPP' => '0'],
            ]
        );
        // User template
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'user', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Your order #[[ order_code ]] has been confirmed!',
                'status' => 1,
                'subject' => 'Order Confirmed - #[[ order_code ]]',
                'template_detail' => '<p>Your order #[[ order_code ]] has been confirmed and is being prepared.</p>',
                'mail_template_detail' => '<p>Dear [[ user_name ]],</p><p>Your order <strong>#[[ order_code ]]</strong> placed on [[ order_date ]] has been confirmed.</p><p>We will notify you once your order is on its way.</p><p>Best regards,<br>[[ company_name ]]<br>[[ company_contact_info ]]</p>',
                'mail_subject' => 'Order Confirmed - #[[ order_code ]]',
                'sms_template_detail' => 'HahuCare: Your order #[[ order_code ]] has been confirmed! We are preparing it now.',
                'sms_subject' => 'Order Confirmed',
                'whatsapp_template_detail' => 'Your order #[[ order_code ]] has been confirmed!',
                'whatsapp_subject' => 'Order Confirmed',
            ]
        );
        // Admin template
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'admin', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Order #[[ order_code ]] has been confirmed.',
                'status' => 1,
                'subject' => 'Order Confirmed - #[[ order_code ]]',
                'template_detail' => '<p>Order #[[ order_code ]] from [[ user_name ]] has been confirmed.</p>',
                'mail_template_detail' => '<p>Dear Admin,</p><p>Order <strong>#[[ order_code ]]</strong> from [[ user_name ]] has been confirmed on [[ order_date ]].</p><p>Best regards,<br>[[ company_name ]]</p>',
                'mail_subject' => 'Order Confirmed - #[[ order_code ]]',
                'sms_template_detail' => 'Order #[[ order_code ]] from [[ user_name ]] has been confirmed.',
                'sms_subject' => 'Order Confirmed',
                'whatsapp_template_detail' => 'Order #[[ order_code ]] confirmed.',
                'whatsapp_subject' => 'Order Confirmed',
            ]
        );

        // --- Order Processing ---
        $template = NotificationTemplate::updateOrCreate(
            ['type' => 'order_processing'],
            [
                'name' => 'order_processing',
                'label' => 'Order Processing',
                'status' => 1,
                'to' => '["user","admin"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '1', 'IS_WHATSAPP' => '0'],
            ]
        );
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'user', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Your order #[[ order_code ]] is now being processed.',
                'status' => 1,
                'subject' => 'Order Processing - #[[ order_code ]]',
                'template_detail' => '<p>Your order #[[ order_code ]] is being processed.</p>',
                'mail_template_detail' => '<p>Dear [[ user_name ]],</p><p>Your order <strong>#[[ order_code ]]</strong> is now being processed. We will keep you updated.</p><p>Best regards,<br>[[ company_name ]]<br>[[ company_contact_info ]]</p>',
                'mail_subject' => 'Order Processing - #[[ order_code ]]',
                'sms_template_detail' => 'HahuCare: Your order #[[ order_code ]] is now being processed.',
                'sms_subject' => 'Order Processing',
                'whatsapp_template_detail' => 'Your order #[[ order_code ]] is being processed.',
                'whatsapp_subject' => 'Order Processing',
            ]
        );
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'admin', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Order #[[ order_code ]] is being processed.',
                'status' => 1,
                'subject' => 'Order Processing - #[[ order_code ]]',
                'template_detail' => '<p>Order #[[ order_code ]] is being processed.</p>',
                'mail_template_detail' => '<p>Dear Admin,</p><p>Order <strong>#[[ order_code ]]</strong> from [[ user_name ]] is now being processed.</p><p>Best regards,<br>[[ company_name ]]</p>',
                'mail_subject' => 'Order Processing - #[[ order_code ]]',
                'sms_template_detail' => 'Order #[[ order_code ]] from [[ user_name ]] is being processed.',
                'sms_subject' => 'Order Processing',
                'whatsapp_template_detail' => 'Order #[[ order_code ]] processing.',
                'whatsapp_subject' => 'Order Processing',
            ]
        );

        // --- Order Out For Delivery ---
        $template = NotificationTemplate::updateOrCreate(
            ['type' => 'order_out_for_delivery'],
            [
                'name' => 'order_out_for_delivery',
                'label' => 'Order Out For Delivery',
                'status' => 1,
                'to' => '["user","admin"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '1', 'IS_WHATSAPP' => '0'],
            ]
        );
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'user', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Your order #[[ order_code ]] is out for delivery!',
                'status' => 1,
                'subject' => 'Order Out For Delivery - #[[ order_code ]]',
                'template_detail' => '<p>Your order #[[ order_code ]] is out for delivery!</p>',
                'mail_template_detail' => '<p>Dear [[ user_name ]],</p><p>Great news! Your order <strong>#[[ order_code ]]</strong> is now out for delivery. Please be ready to receive it.</p><p>Best regards,<br>[[ company_name ]]<br>[[ company_contact_info ]]</p>',
                'mail_subject' => 'Order Out For Delivery - #[[ order_code ]]',
                'sms_template_detail' => 'HahuCare: Your order #[[ order_code ]] is out for delivery! Please be ready to receive it.',
                'sms_subject' => 'Order Out For Delivery',
                'whatsapp_template_detail' => 'Your order #[[ order_code ]] is out for delivery!',
                'whatsapp_subject' => 'Order Out For Delivery',
            ]
        );
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'admin', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Order #[[ order_code ]] is out for delivery.',
                'status' => 1,
                'subject' => 'Order Out For Delivery - #[[ order_code ]]',
                'template_detail' => '<p>Order #[[ order_code ]] is out for delivery.</p>',
                'mail_template_detail' => '<p>Dear Admin,</p><p>Order <strong>#[[ order_code ]]</strong> for [[ user_name ]] is now out for delivery.</p><p>Best regards,<br>[[ company_name ]]</p>',
                'mail_subject' => 'Order Out For Delivery - #[[ order_code ]]',
                'sms_template_detail' => 'Order #[[ order_code ]] for [[ user_name ]] is out for delivery.',
                'sms_subject' => 'Order Out For Delivery',
                'whatsapp_template_detail' => 'Order #[[ order_code ]] out for delivery.',
                'whatsapp_subject' => 'Order Out For Delivery',
            ]
        );

        // --- Order Delivered ---
        $template = NotificationTemplate::updateOrCreate(
            ['type' => 'order_delivered'],
            [
                'name' => 'order_delivered',
                'label' => 'Order Delivered',
                'status' => 1,
                'to' => '["user","admin"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '1', 'IS_WHATSAPP' => '0'],
            ]
        );
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'user', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Your order #[[ order_code ]] has been delivered!',
                'status' => 1,
                'subject' => 'Order Delivered - #[[ order_code ]]',
                'template_detail' => '<p>Your order #[[ order_code ]] has been delivered successfully!</p>',
                'mail_template_detail' => '<p>Dear [[ user_name ]],</p><p>Your order <strong>#[[ order_code ]]</strong> has been delivered successfully! Thank you for choosing HahuCare.</p><p>Best regards,<br>[[ company_name ]]<br>[[ company_contact_info ]]</p>',
                'mail_subject' => 'Order Delivered - #[[ order_code ]]',
                'sms_template_detail' => 'HahuCare: Your order #[[ order_code ]] has been delivered! Thank you for your purchase.',
                'sms_subject' => 'Order Delivered',
                'whatsapp_template_detail' => 'Your order #[[ order_code ]] has been delivered!',
                'whatsapp_subject' => 'Order Delivered',
            ]
        );
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'admin', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Order #[[ order_code ]] has been delivered.',
                'status' => 1,
                'subject' => 'Order Delivered - #[[ order_code ]]',
                'template_detail' => '<p>Order #[[ order_code ]] has been delivered to [[ user_name ]].</p>',
                'mail_template_detail' => '<p>Dear Admin,</p><p>Order <strong>#[[ order_code ]]</strong> has been delivered to [[ user_name ]].</p><p>Best regards,<br>[[ company_name ]]</p>',
                'mail_subject' => 'Order Delivered - #[[ order_code ]]',
                'sms_template_detail' => 'Order #[[ order_code ]] delivered to [[ user_name ]].',
                'sms_subject' => 'Order Delivered',
                'whatsapp_template_detail' => 'Order #[[ order_code ]] delivered.',
                'whatsapp_subject' => 'Order Delivered',
            ]
        );

        // --- Order Cancelled ---
        $template = NotificationTemplate::updateOrCreate(
            ['type' => 'order_cancelled'],
            [
                'name' => 'order_cancelled',
                'label' => 'Order Cancelled',
                'status' => 1,
                'to' => '["user","admin"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '1', 'IS_WHATSAPP' => '0'],
            ]
        );
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'user', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Your order #[[ order_code ]] has been cancelled.',
                'status' => 1,
                'subject' => 'Order Cancelled - #[[ order_code ]]',
                'template_detail' => '<p>Your order #[[ order_code ]] has been cancelled.</p>',
                'mail_template_detail' => '<p>Dear [[ user_name ]],</p><p>We regret to inform you that your order <strong>#[[ order_code ]]</strong> has been cancelled. If you have any questions, please contact us.</p><p>Best regards,<br>[[ company_name ]]<br>[[ company_contact_info ]]</p>',
                'mail_subject' => 'Order Cancelled - #[[ order_code ]]',
                'sms_template_detail' => 'HahuCare: Your order #[[ order_code ]] has been cancelled. Contact us if you have questions.',
                'sms_subject' => 'Order Cancelled',
                'whatsapp_template_detail' => 'Your order #[[ order_code ]] has been cancelled.',
                'whatsapp_subject' => 'Order Cancelled',
            ]
        );
        $template->defaultNotificationTemplateMap()->updateOrCreate(
            ['template_id' => $template->id, 'user_type' => 'admin', 'language' => 'en'],
            [
                'notification_link' => '',
                'notification_message' => 'Order #[[ order_code ]] has been cancelled.',
                'status' => 1,
                'subject' => 'Order Cancelled - #[[ order_code ]]',
                'template_detail' => '<p>Order #[[ order_code ]] from [[ user_name ]] has been cancelled.</p>',
                'mail_template_detail' => '<p>Dear Admin,</p><p>Order <strong>#[[ order_code ]]</strong> from [[ user_name ]] has been cancelled.</p><p>Best regards,<br>[[ company_name ]]</p>',
                'mail_subject' => 'Order Cancelled - #[[ order_code ]]',
                'sms_template_detail' => 'Order #[[ order_code ]] from [[ user_name ]] has been cancelled.',
                'sms_subject' => 'Order Cancelled',
                'whatsapp_template_detail' => 'Order #[[ order_code ]] cancelled.',
                'whatsapp_subject' => 'Order Cancelled',
            ]
        );
    }

    public function down(): void
    {
        // Remove order notification templates
        $types = ['order_confirmed', 'order_processing', 'order_out_for_delivery', 'order_delivered', 'order_cancelled'];
        
        $templates = NotificationTemplate::whereIn('type', $types)->get();
        foreach ($templates as $template) {
            DB::table('notification_template_content_mapping')->where('template_id', $template->id)->delete();
        }
        NotificationTemplate::whereIn('type', $types)->forceDelete();

        // Remove constants
        Constant::where('type', 'notification_type')->whereIn('value', $types)->delete();
        Constant::where('type', 'notification_param_button')->whereIn('value', ['order_code', 'order_date', 'order_time'])->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Modules\Constant\Models\Constant;
use Modules\NotificationTemplate\Models\NotificationTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $types = [
            ['type' => 'notification_to', 'value' => 'pharma', 'name' => 'Pharma'],
            ['type' => 'notification_param_button', 'value' => 'refund_amount', 'name' => 'Refund Amount'],
            ['type' => 'notification_param_button', 'value' => 'amount', 'name' => 'Payout Amount'],
            ['type' => 'notification_param_button', 'value' => 'payment_method', 'name' => 'Payment Method'],
            ['type' => 'notification_param_button', 'value' => 'payment_date', 'name' => 'Payment Date'],
            ['type' => 'notification_param_button', 'value' => 'supplier_name', 'name' => 'Supplier Name'],
            ['type' => 'notification_param_button', 'value' => 'prescription_id', 'name' => 'Prescription ID'],
            ['type' => 'notification_param_button', 'value' => 'medicine_name', 'name' => 'Medicine Name'],
            ['type' => 'notification_param_button', 'value' => 'pharma_name', 'name' => 'Pharma Name'],
            ['type' => 'notification_param_button', 'value' => 'expiry_date', 'name' => 'Expiry Date'],
            ['type' => 'notification_param_button', 'value' => 'available_quantity', 'name' => 'Available Quantity'],
            ['type' => 'notification_param_button', 'value' => 'required_quantity', 'name' => 'Required Quantity'],
            ['type' => 'notification_type', 'value' => 'low_stock_alert', 'name' => 'Medicine stock low alert'],
            ['type' => 'notification_type', 'value' => 'add_prescription', 'name' => 'Add Prescription'],
            ['type' => 'notification_type', 'value' => 'add_medicine', 'name' => 'Add Medicine'],
            ['type' => 'notification_type', 'value' => 'add_pharma', 'name' => 'Add Pharma'],
            ['type' => 'notification_type', 'value' => 'expired_medicine', 'name' => 'Expired Medicine'],
            ['type' => 'notification_type', 'value' => 'pharma_payout', 'name' => 'Pharma Payout'],
            ['type' => 'notification_type', 'value' => 'add_supplier', 'name' => 'Add Supplier'],
        ];

        foreach ($types as $value) {
            Constant::updateOrCreate(
                ['type' => $value['type'], 'value' => $value['value']], // match
                $value
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $createTemplate = function ($data, $maps) {
            $template = NotificationTemplate::updateOrCreate(
                ['type' => $data['type']], // unique by type
                $data
            );

            foreach ($maps as $map) {
                $template->defaultNotificationTemplateMap()->updateOrCreate(
                    [
                        'language' => $map['language'],
                        'user_type' => $map['user_type']
                    ],
                    $map
                );
            }
        };

        // -------------------------
        // 1. Low Stock Alert
        // -------------------------
        $createTemplate(
            [
                'type' => 'low_stock_alert',
                'name' => 'low_stock_alert',
                'label' => 'Medicine Stock Alert',
                'status' => 1,
                'to' => '["admin", "pharma","vendor"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ],
            [
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'Medicine "[[ medicine_name ]]" stock is below required quantity. Only [[ available_quantity ]] units left.',
                    'status' => 1,
                    'subject' => 'Low Stock Alert for [[ medicine_name ]]',
                    'user_type' => 'admin',
                    'template_detail' => '<p>Alert: Medicine stock low!</p><ul><li>Name: [[ medicine_name ]]</li><li>Available: [[ available_quantity ]]</li><li>Required: [[ required_quantity ]]</li></ul>',
                    'mail_template_detail' => '<p>Dear Admin,</p>
                        <p>The stock for the following medicine has dropped below the required level:</p>
                        <ul>
                        <li><strong>Medicine:</strong> [[ medicine_name ]]</li>
                        <li><strong>Available Quantity:</strong> [[ available_quantity ]]</li>
                        <li><strong>Required Quantity:</strong> [[ required_quantity ]]</li>
                        </ul>
                        <p>Please take necessary action to restock this item.</p>
                        <p>Regards,<br>Your System</p>',
                    'mail_subject' => 'Low Stock Alert: [[ medicine_name ]]',
                    'sms_template_detail' => 'Medicine [[ medicine_name ]] stock is low. Available: [[ available_quantity ]], Required: [[ required_quantity ]]',
                    'sms_subject' => 'Low Stock Alert',
                    'whatsapp_template_detail' => '<p>Low stock warning!</p><ul><li>Medicine: [[ medicine_name ]]</li><li>Available: [[ available_quantity ]]</li><li>Required: [[ required_quantity ]]</li></ul>',
                    'whatsapp_subject' => 'Medicine Stock Low',
                ],
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'Medicine "[[ medicine_name ]]" stock is below required quantity. Only [[ available_quantity ]] units left.',
                    'status' => 1,
                    'subject' => 'Low Stock Alert for [[ medicine_name ]]',
                    'user_type' => 'vendor',
                    'template_detail' => '<p>Alert: Medicine stock low!</p><ul><li>Name: [[ medicine_name ]]</li><li>Available: [[ available_quantity ]]</li><li>Required: [[ required_quantity ]]</li></ul>',
                    'mail_template_detail' => '<p>Dear Clinic Admin,</p>
                        <p>The stock for the following medicine has dropped below the required level:</p>
                        <ul>
                        <li><strong>Medicine:</strong> [[ medicine_name ]]</li>
                        <li><strong>Available Quantity:</strong> [[ available_quantity ]]</li>
                        <li><strong>Required Quantity:</strong> [[ required_quantity ]]</li>
                        </ul>
                        <p>Please take necessary action to restock this item.</p>
                        <p>Regards,<br>Your System</p>',
                    'mail_subject' => 'Low Stock Alert: [[ medicine_name ]]',
                    'sms_template_detail' => 'Medicine [[ medicine_name ]] stock is low. Available: [[ available_quantity ]], Required: [[ required_quantity ]]',
                    'sms_subject' => 'Low Stock Alert',
                    'whatsapp_template_detail' => '<p>Low stock warning!</p><ul><li>Medicine: [[ medicine_name ]]</li><li>Available: [[ available_quantity ]]</li><li>Required: [[ required_quantity ]]</li></ul>',
                    'whatsapp_subject' => 'Medicine Stock Low',
                ],
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'Medicine "[[ medicine_name ]]" stock is below required quantity. Only [[ available_quantity ]] units left.',
                    'status' => 1,
                    'subject' => 'Low Stock Alert for [[ medicine_name ]]',
                    'user_type' => 'pharma',
                    'template_detail' => '<p>Alert: Medicine stock low!</p><ul><li>Name: [[ medicine_name ]]</li><li>Available: [[ available_quantity ]]</li><li>Required: [[ required_quantity ]]</li></ul>',
                    'mail_template_detail' => '<p>Dear Admin,</p>
                    <p>The stock for the following medicine has dropped below the required level:</p>
                    <ul>
                    <li><strong>Medicine:</strong> [[ medicine_name ]]</li>
                    <li><strong>Available Quantity:</strong> [[ available_quantity ]]</li>
                    <li><strong>Required Quantity:</strong> [[ required_quantity ]]</li>
                    </ul>
                    <p>Please take necessary action to restock this item.</p>
                    <p>Regards,<br>Your System</p>',
                    'mail_subject' => 'Low Stock Alert: [[ medicine_name ]]',
                    'sms_template_detail' => 'Medicine [[ medicine_name ]] stock is low. Available: [[ available_quantity ]], Required: [[ required_quantity ]]',
                    'sms_subject' => 'Low Stock Alert',
                    'whatsapp_template_detail' => '<p>Low stock warning!</p><ul><li>Medicine: [[ medicine_name ]]</li><li>Available: [[ available_quantity ]]</li><li>Required: [[ required_quantity ]]</li></ul>',
                    'whatsapp_subject' => 'Medicine Stock Low',
                ],
            ]
        );

         // -------------------------
        // 2. Add Prescription
        // -------------------------
        $createTemplate(
            [
                'type' => 'add_prescription',
                'name' => 'add_prescription',
                'label' => 'Add Prescription',
                'status' => 1,
                'to' => '["pharma"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ],
            [
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new prescription #[[ prescription_id ]] for [[ medicine_name ]] has been added.',
                    'status' => 1,
                    'subject' => 'New Prescription Added',
                    'user_type' => 'pharma',
                    'template_detail' => '<p>Prescription #[[ prescription_id ]] for [[ medicine_name ]] has been added.</p>',
                    'mail_template_detail' => '<p>Dear Pharma,</p><p>A new prescription #[[ prescription_id ]] for [[ medicine_name ]] has been added.</p>',
                    'mail_subject' => 'New Prescription Added',
                    'sms_template_detail' => 'New prescription #[[ prescription_id ]] for [[ medicine_name ]] added.',
                    'sms_subject' => 'New Prescription',
                    'whatsapp_template_detail' => 'New prescription #[[ prescription_id ]] for [[ medicine_name ]] added.',
                    'whatsapp_subject' => 'New Prescription',
                ],
            ]
        );

        // -------------------------
        // 3. Add Medicine
        // -------------------------
        $createTemplate(
            [
                'type' => 'add_medicine',
                'name' => 'add_medicine',
                'label' => 'Add Medicine',
                'status' => 1,
                'to' => '["doctor","admin","vendor","pharma"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ],
            [
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new medicine [[ medicine_name ]] has been added to the system.',
                    'status' => 1,
                    'subject' => 'New Medicine Added',
                    'user_type' => 'doctor',
                    'template_detail' => '<p>A new medicine <strong>[[ medicine_name ]]</strong> has been successfully added to the system.</p><p>You can now prescribe this medicine to your patients.</p>',
                    'mail_template_detail' => '<p>Dear Doctor,</p><p>A new medicine <strong>[[ medicine_name ]]</strong> has been successfully added to the system.</p><p>You can now prescribe this medicine to your patients. Please review the medicine details and dosage information.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'New Medicine Added - [[ medicine_name ]]',
                    'sms_template_detail' => 'New medicine [[ medicine_name ]] has been added. You can now prescribe it to patients.',
                    'sms_subject' => 'New Medicine Added',
                    'whatsapp_template_detail' => 'New medicine [[ medicine_name ]] has been added to the system. You can now prescribe it to your patients.',
                    'whatsapp_subject' => 'New Medicine Added',
                ],
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new medicine [[ medicine_name ]] has been added to the system.',
                    'status' => 1,
                    'subject' => 'New Medicine Added',
                    'user_type' => 'admin',
                    'template_detail' => '<p>A new medicine <strong>[[ medicine_name ]]</strong> has been successfully added to the system.</p><p>Please review the medicine details and ensure all information is accurate.</p>',
                    'mail_template_detail' => '<p>Dear Administrator,</p><p>A new medicine <strong>[[ medicine_name ]]</strong> has been successfully added to the system.</p><p>Please review the medicine details and ensure all information is accurate.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'New Medicine Added - [[ medicine_name ]]',
                    'sms_template_detail' => 'New medicine [[ medicine_name ]] has been added to the system. Please review.',
                    'sms_subject' => 'New Medicine Added',
                    'whatsapp_template_detail' => 'New medicine [[ medicine_name ]] has been added to the system. Please review the details.',
                    'whatsapp_subject' => 'New Medicine Added',
                ],
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new medicine [[ medicine_name ]] has been added to the system.',
                    'status' => 1,
                    'subject' => 'New Medicine Added',
                    'user_type' => 'vendor',
                    'template_detail' => '<p>A new medicine <strong>[[ medicine_name ]]</strong> has been successfully added to the system.</p><p>Please ensure you have sufficient stock available.</p>',
                    'mail_template_detail' => '<p>Dear Vendor,</p><p>A new medicine <strong>[[ medicine_name ]]</strong> has been successfully added to the system.</p><p>Please ensure you have sufficient stock available and update your inventory accordingly.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'New Medicine Added - [[ medicine_name ]]',
                    'sms_template_detail' => 'New medicine [[ medicine_name ]] has been added. Please check your inventory.',
                    'sms_subject' => 'New Medicine Added',
                    'whatsapp_template_detail' => 'New medicine [[ medicine_name ]] has been added to the system. Please ensure sufficient stock availability.',
                    'whatsapp_subject' => 'New Medicine Added',
                ],
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new medicine [[ medicine_name ]] has been added to the system.',
                    'status' => 1,
                    'subject' => 'New Medicine Added',
                    'user_type' => 'pharma',
                    'template_detail' => '<p>A new medicine <strong>[[ medicine_name ]]</strong> has been successfully added to the system.</p><p>Please ensure you have sufficient stock available.</p>',
                    'mail_template_detail' => '<p>Dear Pharma,</p><p>A new medicine <strong>[[ medicine_name ]]</strong> has been successfully added to the system.</p><p>Please ensure you have sufficient stock available and update your inventory accordingly.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'New Medicine Added - [[ medicine_name ]]',
                    'sms_template_detail' => 'New medicine [[ medicine_name ]] has been added. Please check your inventory.',
                    'sms_subject' => 'New Medicine Added',
                    'whatsapp_template_detail' => 'New medicine [[ medicine_name ]] has been added to the system. Please ensure sufficient stock availability.',
                    'whatsapp_subject' => 'New Medicine Added',
                ],
            ]
        );

        
        
        // -------------------------
        // 4. Add Pharma
        // -------------------------
        $createTemplate(
            [
                'type' => 'add_pharma',
                'name' => 'add_pharma',
                'label' => 'Add Pharma',
                'status' => 1,
                'to' => '["doctor","admin","vendor"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ],
            [
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new pharma [[ pharma_name ]] has been added to the system.',
                    'status' => 1,
                    'subject' => 'New pharma Added',
                    'user_type' => 'doctor',
                    'template_detail' => '<p>A new pharma <strong>[[ pharma_name ]]</strong> has been successfully added to the system.</p><p>You can now refer patients to this pharma for their prescriptions.</p>',
                    'mail_template_detail' => '<p>Dear Doctor,</p><p>A new pharma <strong>[[ pharma_name ]]</strong> has been successfully added to the system.</p><p>You can now refer patients to this pharma for their prescriptions. Please review the pharma details and contact information.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'New pharma Added - [[ pharma_name ]]',
                    'sms_template_detail' => 'New pharma [[ pharma_name ]] has been added. You can now refer patients there.',
                    'sms_subject' => 'New pharma Added',
                    'whatsapp_template_detail' => 'New pharma [[ pharma_name ]] has been added to the system. You can now refer patients for prescriptions.',
                    'whatsapp_subject' => 'New pharma Added',
                ],
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new pharma [[ pharma_name ]] has been added to the system.',
                    'status' => 1,
                    'subject' => 'New pharma Added',
                    'user_type' => 'admin',
                    'template_detail' => '<p>A new pharma <strong>[[ pharma_name ]]</strong> has been successfully added to the system.</p><p>Please review the pharma details and verify all information is accurate.</p>',
                    'mail_template_detail' => '<p>Dear Administrator,</p><p>A new pharma <strong>[[ pharma_name ]]</strong> has been successfully added to the system.</p><p>Please review the pharma details and verify all information is accurate.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'New pharma Added - [[ pharma_name ]]',
                    'sms_template_detail' => 'New pharma [[ pharma_name ]] has been added to the system. Please review.',
                    'sms_subject' => 'New pharma Added',
                    'whatsapp_template_detail' => 'New pharma [[ pharma_name ]] has been added to the system. Please review the details.',
                    'whatsapp_subject' => 'New pharma Added',
                ],
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new pharma [[ pharma_name ]] has been added to the system.',
                    'status' => 1,
                    'subject' => 'New pharma Added',
                    'user_type' => 'vendor',
                    'template_detail' => '<p>A new pharma <strong>[[ pharma_name ]]</strong> has been successfully added to the system.</p><p>This pharma may be a potential partner for your business.</p>',
                    'mail_template_detail' => '<p>Dear Vendor,</p><p>A new pharma <strong>[[ pharma_name ]]</strong> has been successfully added to the system.</p><p>This pharma may be a potential partner for your business. Please review their details and consider reaching out for collaboration opportunities.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'New Pharma Added - [[ pharma_name ]]',
                    'sms_template_detail' => 'New pharma [[ pharma_name ]] has been added. Consider partnership opportunities.',
                    'sms_subject' => 'New pharma Added',
                    'whatsapp_template_detail' => 'New pharma [[ pharma_name ]] has been added to the system. Consider partnership opportunities.',
                    'whatsapp_subject' => 'New pharma Added',
                ],
            ]
        );

  // -------------------------
        // 5. Expired Medicine
        // -------------------------
        $createTemplate(
            [
                'type' => 'expired_medicine',
                'name' => 'expired_medicine',
                'label' => 'Expired Medicine',
                'status' => 1,
                'to' => '["pharma"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],    
            ],
            [
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'Medicine [[ medicine_name ]] has expired on [[ expiry_date ]]. Please remove from inventory.',
                    'status' => 1,
                    'subject' => 'Medicine Expired',
                    'user_type' => 'pharma',
                    'template_detail' => '<p><strong>URGENT:</strong> Medicine <strong>[[ medicine_name ]]</strong> has expired on <strong>[[ expiry_date ]]</strong>.</p><p>Please immediately remove this medicine from your inventory and dispose of it properly according to safety guidelines.</p>',
                    'mail_template_detail' => '<p>Dear Pharma,</p><p><strong>URGENT:</strong> Medicine <strong>[[ medicine_name ]]</strong> has expired on <strong>[[ expiry_date ]]</strong>.</p><p>Please immediately remove this medicine from your inventory and dispose of it properly according to safety guidelines.</p><p>This is important for patient safety and regulatory compliance.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'URGENT: Medicine Expired - [[ medicine_name ]]',
                    'sms_template_detail' => 'URGENT: Medicine [[ medicine_name ]] expired on [[ expiry_date ]]. Remove from inventory immediately.',
                    'sms_subject' => 'Medicine Expired',
                    'whatsapp_template_detail' => 'URGENT: Medicine [[ medicine_name ]] has expired on [[ expiry_date ]]. Please remove from inventory immediately for patient safety.',
                    'whatsapp_subject' => 'Medicine Expired',
                ],
            ]
        );
        
       // -------------------------
        // 6. Pharma Payout
        // -------------------------
        $createTemplate(
            [
                'type' => 'pharma_payout',
                'name' => 'pharma_payout',
                'label' => 'Pharma Payout',
                'status' => 1,
                'to' => '["pharma"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ],
            [
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'Your payout of [[ amount ]] has been successfully processed via [[ payment_method ]] on [[ payment_date ]].',
                    'status' => 1,
                    'subject' => 'Payout Processed',
                    'user_type' => 'pharma',
                    'template_detail' => '<p>Your payout of <strong>[[ amount ]]</strong> has been successfully processed.</p><p><strong>Payment Details:</strong></p><ul><li>Amount: [[ amount ]]</li><li>Payment Method: [[ payment_method ]]</li><li>Payment Date: [[ payment_date ]]</li><li>Description: [[ description ]]</li></ul><p>The funds should be credited to your registered bank account within 2-3 business days.</p>',
                    'mail_template_detail' => '<p>Dear Pharma,</p><p>Your payout has been successfully processed. Here are the details:</p><p><strong>Payment Details:</strong></p><ul><li>Amount: [[ amount ]]</li><li>Payment Method: [[ payment_method ]]</li><li>Payment Date: [[ payment_date ]]</li><li>Description: [[ description ]]</li></ul><p>The funds should be credited to your registered bank account within 2-3 business days.</p><p>If you have any questions about this transaction, please contact our support team.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'Payout Processed - [[ amount ]]',
                    'sms_template_detail' => 'Your payout of [[ amount ]] has been processed via [[ payment_method ]] on [[ payment_date ]]. Funds will be credited within 2-3 business days.',
                    'sms_subject' => 'Payout Processed',
                    'whatsapp_template_detail' => 'Your payout of [[ amount ]] has been successfully processed via [[ payment_method ]] on [[ payment_date ]]. Funds will be credited to your account within 2-3 business days.',
                    'whatsapp_subject' => 'Payout Processed',
                ],
            ]
        );

        // -------------------------
        // 7. Add Supplier
        // -------------------------
        $createTemplate(
            [
                'type' => 'add_supplier',
                'name' => 'add_supplier',
                'label' => 'Add Supplier',
                'status' => 1,
                'to' => '["pharma"]',
                'channels' => ['IS_MAIL' => '0', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ],
            [
                [
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => 'A new supplier [[ supplier_name ]] has been added to the system.',
                    'status' => 1,
                    'subject' => 'New Supplier Added',
                    'user_type' => 'pharma',
                    'template_detail' => '<p>A new supplier <strong>[[ supplier_name ]]</strong> has been successfully added to the system.</p><p>You can now source medicines and supplies from this supplier.</p>',
                    'mail_template_detail' => '<p>Dear Pharma,</p><p>A new supplier <strong>[[ supplier_name ]]</strong> has been successfully added to the system.</p><p>You can now source medicines and supplies from this supplier. Please review their details and contact information for potential business partnerships.</p><p>Best regards,<br>Health & Wellness Team</p>',
                    'mail_subject' => 'New Supplier Added',
                    'sms_template_detail' => 'New supplier [[ supplier_name ]] has been added. You can now source from them.',
                    'sms_subject' => 'New Supplier Added',
                    'whatsapp_template_detail' => 'New supplier [[ supplier_name ]] has been added to the system. You can now source medicines and supplies from them.',
                    'whatsapp_subject' => 'New Supplier Added',
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NotificationTemplate::whereIn('type', [
            'low_stock_alert',
            'add_prescription',
            'add_medicine',
            'add_pharma',
            'expired_medicine',
            'pharma_payout',
            'add_supplier',
        ])->delete();
    }
};

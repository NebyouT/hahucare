<?php

namespace Modules\Pharma\Traits;

trait PharmaOwnershipChecker
{

    // All Medicine 
    public function ensureHasEditPermission()
    {
        $user = auth()->user();

        if (
            !$user->hasRole(['admin', 'demo_admin']) &&
            !$user->can('edit_medicine')
        ) {
            abort(403, 'Unauthorized access');
        }
    }

    public function ensureCanCreate()
    {
        $user = auth()->user();
    
        if (
            !$user->hasAnyRole(['admin', 'demo_admin']) &&
            !($user->hasRole('pharma') && $user->can('add_medicine'))
        ) {
            abort(403, 'You do not have permission to create medicine.');
        }
    }
    
    public function ensureCanDelete()
    {
        $user = auth()->user();

        if (
            !$user->hasRole(['admin', 'demo_admin']) &&
            !$user->can('delete_medicine')
        ) {
            abort(403, 'You do not have permission to delete this item.');
        }
    }

    // Prescription
    public function ensureCanEditPrescription()
    {
        $user = auth()->user();
    
        if (
            !$user->hasRole(['admin', 'demo_admin']) &&
            !$user->can('edit_prescription')
        ) {
            abort(403, 'You do not have permission to edit this prescription.');
        }
    }
    
    public function ensureCanDeletePrescription()
    {
        $user = auth()->user();
    
        if (
            !$user->hasRole(['admin', 'demo_admin']) &&
            !$user->can('delete_prescription')
        ) {
            abort(403, 'You do not have permission to delete this prescription.');
        }
    }

    // Supplier
    public function canCreateSupplier()
    {
        $user = auth()->user();
    
        if (
            !$user->hasAnyRole(['admin', 'demo_admin','vendor']) &&
            !($user->hasRole('pharma') && $user->can('add_suppliers'))
        ) {
            abort(403, 'You do not have permission to create supplier.');
        }
    }

    public function canEditSupplier()
    {
        $user = auth()->user();
    
        if (
            !$user->hasRole(['admin', 'demo_admin','vendor']) &&
            !$user->can('edit_suppliers')
        ) {
            abort(403, 'You do not have permission to edit this supplier.');
        }
    }
    
    public function canDeleteSupplier()
    {
        $user = auth()->user();
    
        if (
            !$user->hasRole(['admin', 'demo_admin','vendor']) &&
            !$user->can('delete_suppliers')
        ) {
            abort(403, 'You do not have permission to delete this supplier.');
        }
    }

    public function ensurePharmaOwns($model)
    {
        $user = auth()->user();
        if ($user->hasRole(['admin', 'demo_admin','vendor'])) {
            return;
        }
        if ($user->id !== $model->pharma_id) {
            abort(403, 'You are not authorized to access this.');
        }
    }

    public function ensurePharmaOwnsPrescription($model)
        {
            $user = auth()->user();
            if ($user->hasRole(['admin', 'demo_admin', 'pharma','vendor'])) {
                return;
            }

            if ($user->id !== $model->pharma_id) {
                abort(403, 'You are not authorized to access this prescription.');
            }
        }


    public function ensurePharmaOwnsSupplier($supplier)
        {
            $user = auth()->user();

            if ($user->hasRole(['admin', 'demo_admin','vendor'])) {
                return;
            }

            if ($user->id !== $supplier->pharma_id) {
                abort(403, 'You are not authorized to access this supplier.');
            }
        }

    
}

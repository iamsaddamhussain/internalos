<?php

namespace App\Traits;

trait HasPlanLimits
{
    public function getPlanLimits()
    {
        $limits = [
            'free' => [
                'max_collections' => 3,
                'max_records' => 500,
                'max_users' => 3,
                'max_workspaces_per_user' => 1,
                'features' => [
                    'basic_fields' => true,
                    'advanced_fields' => false,
                    'export' => false,
                    'api_access' => false,
                    'audit_logs' => false,
                    'sso' => false,
                    'webhooks' => false,
                ],
            ],
            'premium' => [
                'max_collections' => 999999, // unlimited
                'max_records' => 50000,
                'max_users' => 20,
                'max_workspaces_per_user' => 5,
                'features' => [
                    'basic_fields' => true,
                    'advanced_fields' => true,
                    'export' => true,
                    'api_access' => false,
                    'audit_logs' => false,
                    'sso' => false,
                    'webhooks' => false,
                ],
            ],
            'ultra_premium' => [
                'max_collections' => 999999,
                'max_records' => 999999,
                'max_users' => 999999,
                'max_workspaces_per_user' => 999999,
                'features' => [
                    'basic_fields' => true,
                    'advanced_fields' => true,
                    'export' => true,
                    'api_access' => true,
                    'audit_logs' => true,
                    'sso' => true,
                    'webhooks' => true,
                ],
            ],
        ];

        return $limits[$this->plan] ?? $limits['free'];
    }

    public function canAddCollection()
    {
        $limits = $this->getPlanLimits();
        $currentCount = $this->collections()->count();
        return $currentCount < $limits['max_collections'];
    }

    public function canAddRecord()
    {
        $limits = $this->getPlanLimits();
        $currentCount = $this->collections()->withCount('records')->get()->sum('records_count');
        return $currentCount < $limits['max_records'];
    }

    public function canAddUser()
    {
        $limits = $this->getPlanLimits();
        $currentCount = $this->users()->count();
        return $currentCount < $limits['max_users'];
    }

    public function hasFeature($feature)
    {
        $limits = $this->getPlanLimits();
        return $limits['features'][$feature] ?? false;
    }

    public function getPlanName()
    {
        $names = [
            'free' => 'Free',
            'premium' => 'Premium',
            'ultra_premium' => 'Ultra Premium',
        ];
        return $names[$this->plan] ?? 'Free';
    }

    public function isAtLimit($type)
    {
        switch ($type) {
            case 'collections':
                return !$this->canAddCollection();
            case 'records':
                return !$this->canAddRecord();
            case 'users':
                return !$this->canAddUser();
            default:
                return false;
        }
    }
}

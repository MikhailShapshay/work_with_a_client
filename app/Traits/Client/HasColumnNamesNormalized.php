<?php

namespace App\Traits\Client;

use App\Model;
use App\Stringy;
use Carbon\Carbon;

trait HasColumnNamesNormalized
{
    public function getIdAttribute()
    {
        return $this->client_code1c;
    }

    public function getNameAttribute()
    {
        return $this->client_name;
    }

    public function getFilialIdAttribute()
    {
        return $this->client_filial_code1c;
    }

    public function getAddressesAttribute()
    {
        if (! $this->client_addr) {
            return [];
        }

        return Stringy::create($this->client_addr)->explode('(#)');
    }

    public function getConsignmentAllowedAttribute()
    {
        return $this->client_consignment;
    }

    public function getIinAttribute()
    {
        return $this->client_inn;
    }

    public function getDenyRemoteStoresAttribute()
    {
        return $this->client_deny_remote_store;
    }

    public function getPartnersForbiddenAttribute()
    {
        return $this->client_deny_remote_store;
    }

    public function getDebtAttribute()
    {
        return $this->client_debd;
    }

    public function getDueDebtAttribute()
    {
        return $this->client_due_debd;
    }

    public function getDueDebtDaysAttribute()
    {
        return $this->client_due_debd_days;
    }

    public function getIsVerifiedAttribute()
    {
        return ! ($this->notverified == 1);
    }

    public function getHasRetailBuyerAgreementAttribute()
    {
        return (bool) $this->client_is_phys_agreement;
    }

    public function getRetailOnlyAttribute()
    {
        return (bool) $this->client_is_phys;
    }

    public function getOverpaymentAttribute()
    {
        if ($this->client_debd < 0) {
            return abs($this->client_debd);
        }

        return 0;
    }

    public function getClientPayDateAttribute($value)
    {
        if ($value == Model::EMPTY_DATETIME) {
            return null;
        }

        if ($value == null) {
            return null;
        }

        return Carbon::create($value);
    }
}

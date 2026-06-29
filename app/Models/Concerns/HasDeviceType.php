<?php

namespace App\Models\Concerns;

trait HasDeviceType
{
    public function deviceTypeLabel(): ?string
    {
        if (! $this->device_type) {
            return null;
        }

        return config('edusphere.device_types.'.$this->device_type.'.label');
    }

    public function deviceTypeIcon(): ?string
    {
        if (! $this->device_type) {
            return null;
        }

        return config('edusphere.device_types.'.$this->device_type.'.icon');
    }

    /** @return array<string, string>|null */
    public function deviceTypeMeta(): ?array
    {
        if (! $this->device_type) {
            return null;
        }

        $meta = config('edusphere.device_types.'.$this->device_type);

        return is_array($meta) ? $meta : null;
    }
}

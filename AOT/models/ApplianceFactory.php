<?php


require_once __DIR__ . '/DeviceModel.php';

// ── Base appliance ─────────────────────────────────────────────────────────
abstract class Appliance
{
    protected array $data = [];

    public function __construct(array $attributes = [])
    {
        $this->data = array_merge($this->defaults(), $attributes);
    }

    /** Subclasses define sensible defaults */
    abstract protected function defaults(): array;

    /** Persist to devices table */
    public function save(): int
    {
        return (new DeviceModel())->create($this->data);
    }

    public function getData(): array
    {
        return $this->data;
    }
}

// ── Concrete appliances ────────────────────────────────────────────────────
class ACAppliance extends Appliance
{
    protected function defaults(): array
    {
        return [
            'type'        => 'ac',
            'category'    => 'cooling',
            'icon'        => '❄️',
            'status'      => 'off',
            'location'    => 'Living Room',
        ];
    }
}

class RefrigeratorAppliance extends Appliance
{
    protected function defaults(): array
    {
        return [
            'type'        => 'refrigerator',
            'category'    => 'kitchen',
            'icon'        => '🧊',
            'status'      => 'on',
            'location'    => 'Kitchen',
        ];
    }
}

class WashingMachineAppliance extends Appliance
{
    protected function defaults(): array
    {
        return [
            'type'        => 'washing_machine',
            'category'    => 'laundry',
            'icon'        => '🌀',
            'status'      => 'off',
            'location'    => 'Laundry Room',
        ];
    }
}

class LightAppliance extends Appliance
{
    protected function defaults(): array
    {
        return [
            'type'        => 'light',
            'category'    => 'lighting',
            'icon'        => '💡',
            'status'      => 'off',
            'location'    => 'Room',
        ];
    }
}

class WaterHeaterAppliance extends Appliance
{
    protected function defaults(): array
    {
        return [
            'type'        => 'water_heater',
            'category'    => 'heating',
            'icon'        => '🔥',
            'status'      => 'off',
            'location'    => 'Bathroom',
        ];
    }
}

class SolarPanelAppliance extends Appliance
{
    protected function defaults(): array
    {
        return [
            'type'        => 'solar_panel',
            'category'    => 'solar',
            'icon'        => '☀️',
            'status'      => 'on',
            'location'    => 'Roof',
        ];
    }
}

class GenericAppliance extends Appliance
{
    protected function defaults(): array
    {
        return [
            'type'        => 'generic',
            'category'    => 'other',
            'icon'        => '🔌',
            'status'      => 'off',
            'location'    => 'Home',
        ];
    }
}

// ── Factory ────────────────────────────────────────────────────────────────
class ApplianceFactory
{
    private static array $map = [
        'ac'              => ACAppliance::class,
        'air_conditioner' => ACAppliance::class,
        'refrigerator'    => RefrigeratorAppliance::class,
        'fridge'          => RefrigeratorAppliance::class,
        'washing_machine' => WashingMachineAppliance::class,
        'washer'          => WashingMachineAppliance::class,
        'light'           => LightAppliance::class,
        'lamp'            => LightAppliance::class,
        'water_heater'    => WaterHeaterAppliance::class,
        'heater'          => WaterHeaterAppliance::class,
        'solar_panel'     => SolarPanelAppliance::class,
        'solar'           => SolarPanelAppliance::class,
    ];

    /**
     * @param string $type       Device type key
     * @param array  $attributes Override / extra attributes
     */
    public static function create(string $type, array $attributes = []): Appliance
    {
        $class = self::$map[strtolower($type)] ?? GenericAppliance::class;
        return new $class($attributes);
    }

    /** List all registered type keys */
    public static function types(): array
    {
        return array_keys(self::$map);
    }
}

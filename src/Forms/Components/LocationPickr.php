<?php

namespace Treii28\FilamentLocationPickrField\Forms\Components;

use Closure;
use Exception;
use Filament\Forms\Components\Field;
use JsonException;

class LocationPickr extends Field
{
    protected string $view = 'filament.forms.components.locationpickr';

    private int $precision = 8;

    protected array | Closure | null $defaultLocation = [45.158, -84.245];

    protected float | Closure $defaultZoom = 12.8;

    protected bool | Closure $draggable = true;

    protected bool | Closure $clickable = false;

    protected array | Closure $mapControls = [];

    protected array | Closure $latLngBounds = [];

    protected string | Closure $height = '400px';

    protected string | Closure | null $myLocationButtonLabel = null;

    protected string | Closure | null $overlayUrl = null;
    protected array | Closure $overlayBounds = [];
    protected float | Closure | null $overlayOpacity = 0.25;

    private array $mapConfig = [
        'draggable' => true,
        'clickable' => false,
        'defaultLocation' => [
            'lat' => 45.158,
            'lng' => -84.245,
        ],
        'controls' => [],
        'statePath' => '',
        'defaultZoom' => 12.8,
        'myLocationButtonLabel' => '',
        'apiKey' => '',
    ];

    public array $overlayConfig = [
        'overlayUrl' => '',
        //'overlayStyle' => [],
        'overlayBounds' => [
            'north' => 45.21,
            'east' => -84.2,
            'south' => 45.11,
            'west' => -84.29,
        ],
        'overlayOpacity' => 0.25
    ];

    public array $controls = [
        'mapTypeControl' => true,
        'scaleControl' => true,
        'streetViewControl' => false,
        'rotateControl' => false,
        'fullscreenControl' => false,
        'zoomControl' => true,
    ];

    public function defaultLocation(array | Closure $defaultLocation): static
    {
        $this->defaultLocation = $defaultLocation;

        return $this;
    }

    public function getDefaultLocation(): array
    {
        $position = $this->evaluate($this->defaultLocation);

        if (is_array($position)) {
            if (array_key_exists('lat', $position) && array_key_exists('lng', $position)) {
                return $position;
            } elseif (is_numeric($position[0]) && is_numeric($position[1])) {
                return [
                    'lat' => is_string($position[0]) ? round(floatval($position[0]), $this->precision) : $position[0],
                    'lng' => is_string($position[1]) ? round(floatval($position[1]), $this->precision) : $position[1],
                ];
            }
        }

        return config('filament-locationpickr-field.default_location');
    }

    public function defaultZoom(int | float | Closure $defaultZoom): static
    {
        $this->defaultZoom = $defaultZoom;

        return $this;
    }

    public function getDefaultZoom(): int | float
    {
        $zoom = $this->evaluate($this->defaultZoom);

        if (is_numeric($zoom)) {
            return $zoom;
        }

        return config('filament-locationpickr-field.default_zoom');
    }

    public function draggable(bool | Closure $draggable = true): static
    {
        $this->draggable = $draggable;

        return $this;
    }

    public function getDraggable(): bool
    {
        return $this->evaluate($this->draggable) ?? config('filament-locationpickr-field.default_draggable');
    }

    public function clickable(bool | Closure $clickable = true): static
    {
        $this->clickable = $clickable;

        return $this;
    }

    public function getClickable(): bool
    {
        return $this->evaluate($this->clickable) ?? config('filament-locationpickr-field.default_clickable');
    }

    public function mapControls(array | Closure $controls): static
    {
        $this->mapControls = $controls;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function getMapControls(): string
    {
        $controls = $this->evaluate($this->mapControls) ?? [];

        return json_encode(array_merge($this->controls, $controls), JSON_THROW_ON_ERROR);
    }

    public function latLngBounds(array | Closure $latLngBounds): static
    {
        $this->latLngBounds = $latLngBounds;

        return $this;
    }

    public function getLatLngBounds(): string | null
    {
        if(count($this->latLngBounds) == 0)
            return null;

        $latLngBounds = $this->evaluate($this->latLngBounds) ?? [];

        return json_encode($latLngBounds);
    }

    public function height(string | Closure $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): string
    {
        return $this->evaluate($this->height) ?? config('filament-locationpickr-field.default_height');
    }

    public function myLocationButtonLabel(string | Closure $myLocationButtonLabel): static
    {
        $this->myLocationButtonLabel = $myLocationButtonLabel;

        return $this;
    }

    public function getMyLocationButtonLabel(): string
    {
        return $this->evaluate($this->myLocationButtonLabel) ?? config('filament-locationpickr-field.my_location_button');
    }

    public function ovarlayUrl(string| Closure $overlayUrl): static
    {
        $this->overlayUrl = $overlayUrl;

        return $this;
    }

    public function getOverlayUrl(): string | null
    {
        return $this->evaluate($this->overlayUrl) ?? null;
    }

    public function overlayBounds(array | Closure $overlayBounds): static
    {
        $this->overlayBounds = $overlayBounds;

        return $this;
    }

    public function getOverlayBounds(): string
    {
        $overlayBounds = $this->evaluate($this->overlayConfig['overlayBounds']) ?? [];

        return json_encode($overlayBounds);
    }

    public function getOverlayConfig(): string | null
    {
        if(empty($this->getOverlayUrl()) || (count($this->getOverlayBounds()) == 0))
            return null;

        $configArray = [
            'overlayUrl' => $this->getOverlayUrl(),
            'overlayBounds' => $this->getOverlayBounds(),
            'overlayOpacity' => $this->overlayOpacity
        ];

        return json_encode($this->overlayConfig);
    }

    public function overlayConfig(array | Closure $overlayConfig): static
    {
        $this->overlayConfig = $overlayConfig;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function getMapConfig(): string
    {
        $configArray = array_merge($this->mapConfig, [
            'draggable' => $this->getDraggable(),
            'clickable' => $this->getClickable(),
            'defaultLocation' => $this->getDefaultLocation(),
            'statePath' => $this->getStatePath(),
            'controls' => $this->getMapControls(),
            'defaultZoom' => $this->getDefaultZoom(),
            'myLocationButtonLabel' => $this->getMyLocationButtonLabel(),
            'apiKey' => config('filament-locationpickr-field.key'),
        ]);

        if($latLngBounds = $this->getLatLngBounds())
            $configArray['latLngBounds'] = $latLngBounds;
        if($overlayConfig = $this->getOverlayConfig())
            $configArray['overlayConfig'] = $overlayConfig;

        return json_encode($configArray,JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function getState(): array
    {
        $state = parent::getState();

        if (is_array($state)) {
            return $state;
        } else {
            try {
                return @json_decode($state, true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {
                return $this->getDefaultLocation();
            }
        }
    }
}

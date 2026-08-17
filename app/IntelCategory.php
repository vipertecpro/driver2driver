<?php

namespace App;

use App\Icons\Android;
use App\Icons\Ios;

/**
 * Intel report categories for the Driver-to-Driver demo. Each case carries
 * its full presentation contract — label, platform icons, and the
 * High-Vis Utility color treatment — so views never hardcode category logic.
 */
enum IntelCategory: string
{
    case BuildingAccess = 'access';
    case CleanBathroom = 'bathroom';
    case DogAlert = 'dog';
    case GasPrice = 'gas';
    case TrafficParking = 'traffic';

    /** Short label used on feed cards and map chips. */
    public function label(): string
    {
        return match ($this) {
            self::BuildingAccess => 'Access Point',
            self::CleanBathroom => 'Restroom',
            self::DogAlert => 'Dog Alert',
            self::GasPrice => 'Gas Price',
            self::TrafficParking => 'Traffic/Parking',
        };
    }

    /** Longer label used on the report screen's category grid. */
    public function reportLabel(): string
    {
        return match ($this) {
            self::BuildingAccess => 'Building Access',
            self::CleanBathroom => 'Clean Bathroom',
            self::DogAlert => 'Dog Alert',
            self::GasPrice => 'Gas Price',
            self::TrafficParking => 'Traffic/Parking',
        };
    }

    public function iosIcon(): Ios
    {
        return match ($this) {
            self::BuildingAccess => Ios::KeyFill,
            self::CleanBathroom => Ios::FigureDressLineVerticalFigure,
            self::DogAlert => Ios::PawprintFill,
            self::GasPrice => Ios::FuelpumpFill,
            self::TrafficParking => Ios::TruckBoxFill,
        };
    }

    public function androidIcon(): Android
    {
        return match ($this) {
            self::BuildingAccess => Android::Key,
            self::CleanBathroom => Android::Wc,
            self::DogAlert => Android::Pets,
            self::GasPrice => Android::LocalGasStation,
            self::TrafficParking => Android::LocalShipping,
        };
    }

    /**
     * The theme token stem carrying this category's identity colour. The
     * pairs live in config/native-ui.php so every category gets a day and a
     * night value automatically — hardcoded hex was dark-only and sat at
     * about 1.2:1 on a white card.
     */
    private function token(): string
    {
        return 'cat-'.$this->value;
    }

    /** Left-hand status bar on feed cards (DESIGN.md: color before text). */
    public function barClass(): string
    {
        return 'bg-theme-'.$this->token();
    }

    /** Icon + category label tint on feed cards. */
    public function accentClass(): string
    {
        return 'text-theme-'.$this->token();
    }

    /** Solid fill for map pins — the same identity colour as the card stripe. */
    public function pinClass(): string
    {
        return 'bg-theme-'.$this->token();
    }

    /** Icon color on top of the pin fill. */
    public function pinIconClass(): string
    {
        return 'text-theme-on-'.$this->token();
    }
}

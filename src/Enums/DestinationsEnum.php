<?php

namespace Src\Enums;

enum DestinationsEnum: string
{
    case ZLP = 'zlp'; // Златопіль
    case OLE = 'ole'; // Олексіївка
    case BLV = 'blv'; // Беляївка
    case BRE = 'bre'; // Берека
    case VBI = 'vbi'; // Верхній Бішкін
    case VOR = 'vor'; // Верхня Орілька
    case DMY = 'dmy'; // Дмитрівка
    case YEF = 'yef'; // Єфремівка
    case ZAK = 'zak'; // Закутнівка
    case KAM = 'kam'; // Кам’янка
    case KYS = 'kys'; // Киселі
    case KRA = 'kra'; // Красиве
    case MYR = 'myr'; // Миронівка
    case MYK = 'myk'; // Михайлівка
    case LOZ = 'loz'; // Лозовая
    case BLY = 'bly'; // Близнюки
    case SHU = 'shu'; // Шульське
    case NBR = 'nbr'; // Новоберецьке

    public function title(): string
    {
        return match ($this) {
            self::ZLP => 'Zlatopil',
            self::OLE => 'Oleksiivka',
            self::BLV => 'Belyaivka',
            self::BRE => 'Bereka',
            self::VBI => 'Verkhnii Bishkin',
            self::VOR => 'Verkhnia Orilka',
            self::DMY => 'Dmytrivka',
            self::YEF => 'Yefremivka',
            self::ZAK => 'Zakutnivka',
            self::KAM => 'Kamianka',
            self::KYS => 'Kyseli',
            self::KRA => 'Krasive',
            self::MYR => 'Myronivka',
            self::MYK => 'Mykhailivka',
            self::LOZ => 'Lozova',
            self::BLY => 'Blyzniuky',
            self::SHU => 'Shulske',
            self::NBR => 'Novoberetske',
        };
    }

    public function isVillage(): bool
    {
        return match ($this) {
            self::ZLP,
            self::LOZ,
            self::BLY => false,

            default => true,
        };
    }

    public static function getVillageDestinations(): array
    {
        return array_values(
            array_filter(
                self::cases(),
                static fn (self $case) => $case->isVillage()
            )
        );
    }
}

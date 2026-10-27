import { LayoutDashboard, Route, Trophy, Shield, Calendar, type LucideIcon } from 'lucide-react';

export interface NavItem {
    route: string;
    href: string;
    label: string;
    icon: LucideIcon;
}

/**
 * Navegación principal (header desktop y bottom nav móvil).
 *
 * "Clasificación" siempre está disponible y apunta a la clasificación de la
 * temporada (/season/classification); "Etapa" solo existe con liga activa.
 */
export function buildNavItems(activeLeague: { id: string } | null | undefined, isAdmin: boolean): NavItem[] {
    return [
        { route: 'dashboard', href: route('dashboard'), label: 'Dashboard', icon: LayoutDashboard },
        { route: 'season.index', href: route('season.index'), label: 'Temporada', icon: Calendar },
        ...(activeLeague
            ? [{ route: 'stages.index', href: route('stages.index', activeLeague.id), label: 'Etapa', icon: Route }]
            : []),
        { route: 'season.classification', href: route('season.classification'), label: 'Clasificación', icon: Trophy },
        ...(isAdmin
            ? [{ route: 'admin', href: '/admin', label: 'Admin', icon: Shield }]
            : []),
        // TODO: Descomentar para añadir Pedales al nav
        // { route: 'pedales', href: route('pedales'), label: 'Pedales', icon: PedalesLogo },
    ];
}

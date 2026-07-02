import { Link } from '@inertiajs/react';
import { Menu, MenuButton, MenuItem, MenuItems, Portal } from '@headlessui/react';
import { User, Users, LogOut, ChevronDown, ChevronRight } from 'lucide-react';
import Avatar from '@/components/Avatar';

interface UserMenuProps {
    user: { name: string; email: string; avatar?: string | null };
    leagues: { id: string; name: string }[];
}

export default function UserMenu({ user, leagues }: UserMenuProps) {
    return (
        <Menu as="div" className="relative">
            {/* Desktop trigger */}
            <MenuButton className="hidden md:flex items-center gap-2 rounded-full p-1 pr-3 text-sm transition-colors hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background">
                <Avatar user={user} size="sm" />
                <span className="text-sm text-muted-foreground">{user.name}</span>
                <ChevronDown className="h-3.5 w-3.5 text-muted-foreground" />
            </MenuButton>

            {/* Mobile trigger */}
            <MenuButton className="md:hidden rounded-full p-1 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background">
                <Avatar user={user} size="sm" />
            </MenuButton>

            <Portal>
                <MenuItems
                    transition
                    className="fixed right-4 top-12 z-[100] w-64 origin-top-right rounded-xl border bg-popover p-1.5 shadow-lg outline-none transition duration-100 ease-out data-[closed]:scale-95 data-[closed]:opacity-0 md:right-8 md:w-72"
                >
                    {/* User header */}
                    <div className="px-3 py-2.5">
                        <p className="text-sm font-medium truncate">{user.name}</p>
                        <p className="text-xs text-muted-foreground truncate">{user.email}</p>
                    </div>

                    <div className="h-px bg-border my-1" />

                    {/* Profile */}
                    <MenuItem>
                        <Link
                            href={route('profile.edit')}
                            className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-foreground transition-colors hover:bg-accent hover:text-accent-foreground data-[focus]:bg-accent data-[focus]:text-accent-foreground"
                        >
                            <User className="h-4 w-4 text-muted-foreground" />
                            Mi perfil
                        </Link>
                    </MenuItem>

                    {/* Leagues */}
                    <MenuItem>
                        <Link
                            href={route('leagues.index')}
                            className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-foreground transition-colors hover:bg-accent hover:text-accent-foreground data-[focus]:bg-accent data-[focus]:text-accent-foreground"
                        >
                            <Users className="h-4 w-4 text-muted-foreground" />
                            Mis ligas
                        </Link>
                    </MenuItem>

                    {/* League sub-items */}
                    {leagues.length > 0 && (
                        <div className="ml-4 border-l pl-3 py-1 space-y-0.5">
                            {leagues.map((league) => (
                                <MenuItem key={league.id}>
                                    <Link
                                        href={route('leagues.show', league.id)}
                                        className="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs text-muted-foreground transition-colors truncate hover:bg-accent hover:text-accent-foreground data-[focus]:bg-accent data-[focus]:text-accent-foreground"
                                    >
                                        <ChevronRight className="h-3 w-3 shrink-0" />
                                        <span className="truncate">{league.name}</span>
                                    </Link>
                                </MenuItem>
                            ))}
                            <MenuItem>
                                <Link
                                    href={route('leagues.index')}
                                    className="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-accent-500 transition-colors hover:text-accent-600 data-[focus]:bg-accent data-[focus]:text-accent-foreground"
                                >
                                    Ver todas
                                </Link>
                            </MenuItem>
                        </div>
                    )}

                    <div className="h-px bg-border my-1" />

                    {/* Logout */}
                    <MenuItem>
                        <Link
                            href={route('logout')}
                            method="post"
                            className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive data-[focus]:bg-destructive/10 data-[focus]:text-destructive"
                        >
                            <LogOut className="h-4 w-4" />
                            Cerrar sesión
                        </Link>
                    </MenuItem>
                </MenuItems>
            </Portal>
        </Menu>
    );
}

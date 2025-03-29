import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { CalendarFold, createLucideIcon, Flag, FolderCode, LayoutGrid, Users } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/',
        icon: LayoutGrid,
    },
    {
        title: 'Seasons',
        href: '#',
        icon: CalendarFold,
    },
    {
        title: 'Races',
        href: '#',
        icon: Flag,
    },
    {
        title: 'Drivers',
        href: '#',
        icon: createLucideIcon(
            'DriverHelmet',
            [
                ["path", { d: "M22 12.2a10 10 0 1 0-19.4 3.2c.2.5.8 1.1 1.3 1.3l13.2 5.1c.5.2 1.2 0 1.6-.3l2.6-2.6c.4-.4.7-1.2.7-1.7Z" }],
                ["path", { d: "m21.8 18-10.5-4a2 2.06 0 0 1 .7-4h9.8" }]
            ]
        ),
    },
    {
        title: 'Teams',
        href: '#',
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/Qv1ko/RacingTracker',
        icon: FolderCode,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

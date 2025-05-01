import { ActivityChart } from '@/components/charts/activity-chart';
import { PositionsChart } from '@/components/charts/positions-chart';
import { SinglePointsChart } from '@/components/charts/single-points-chart';
import { Icon } from '@/components/icon';
import FlagIcon from '@/components/ui/flag-icon';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { HelmetIconNode } from '@/lib/utils';
import { Driver, type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Driver profile',
        href: '/drivers',
    },
];

export default function Drivers({ driver }: { driver: Driver }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={driver.name + ' ' + driver.surname} />
            <div className="px-4 py-8">
                <div className="flex justify-center">
                    <div className="mb-8 flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:justify-start">
                        <div className="flex h-16 w-16 items-center justify-center">
                            <Icon iconNode={HelmetIconNode} className="h-12 w-12" />
                        </div>
                        <div className="text-center sm:text-left">
                            <h2 className="text-2xl font-bold">
                                {driver.name} {driver.surname}
                            </h2>
                            {driver.nationality && (
                                <div className="flex items-center gap-2">
                                    <FlagIcon nationality={driver.nationality.toString()} size={24} />
                                    <p>{driver.nationality}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
                <div className="flex h-full flex-1 flex-col gap-4 rounded-sm">
                    <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                    </div>
                    {driver.activity && (
                        <ActivityChart
                            data={{
                                activity: driver.activity.map((activity) => ({
                                    position: activity.status,
                                    name: activity.name,
                                    date: activity.date,
                                })),
                            }}
                        />
                    )}
                    <div className="grid auto-rows-min gap-4 md:grid-cols-2">
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                    </div>
                    {driver.pointsHistory && <SinglePointsChart data={driver.pointsHistory} />}
                    <table key="seasons"></table>
                    {driver.positionsHistory && <PositionsChart data={driver.positionsHistory} />}
                    {((driver.teams?.length || 0) > 0 || driver.teammates) && (
                        <div className="grid auto-rows-min grid-cols-2 gap-4">
                            <div className="grid auto-rows-min justify-items-center gap-4">
                                <div className="flex items-center justify-center gap-2">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-full">
                                        <Users className="h-8 w-8" />
                                    </div>
                                    <h3 className="text-xl font-semibold">Teams</h3>
                                </div>
                                {driver.teams &&
                                    (() => {
                                        const teams = driver.teams.filter((team) => team);
                                        return (
                                            <div
                                                className={
                                                    'grid w-full grid-cols-[repeat(auto-fit,_minmax(150px,_max-content))] justify-center gap-4'
                                                }
                                            >
                                                {teams.map(
                                                    (team, index) =>
                                                        team && (
                                                            <div key={`team-${index}`} className="rounded-sm border px-4 py-2">
                                                                <Link
                                                                    href={`/teams/${team.id}`}
                                                                    className="hover:text-primary flex items-center justify-center gap-2"
                                                                >
                                                                    <FlagIcon nationality={team.nationality || 'unknown'} size={16} /> {team.name}
                                                                </Link>
                                                            </div>
                                                        ),
                                                )}
                                            </div>
                                        );
                                    })()}
                            </div>
                            <div className="grid auto-rows-min justify-items-center gap-4">
                                <div className="flex items-center justify-center gap-2">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-full">
                                        <Icon iconNode={HelmetIconNode} className="h-8 w-8" />
                                    </div>
                                    <h3 className="text-xl font-semibold">Teammates</h3>
                                </div>
                                {driver.teammates &&
                                    (() => {
                                        const teammates = driver.teammates;
                                        return (
                                            <div className="grid w-full grid-cols-[repeat(auto-fit,_minmax(150px,_max-content))] justify-center gap-4">
                                                {teammates.map(
                                                    (teammates, index) =>
                                                        teammates && (
                                                            <div key={`team-${index}`} className="rounded-sm border px-4 py-2">
                                                                <Link
                                                                    href={`/drivers/${teammates.id}`}
                                                                    className="hover:text-primary flex items-center justify-center gap-2"
                                                                >
                                                                    <FlagIcon nationality={teammates.nationality || 'unknown'} size={16} />{' '}
                                                                    {teammates.name[0].toUpperCase()}. {teammates.surname}
                                                                </Link>
                                                            </div>
                                                        ),
                                                )}
                                            </div>
                                        );
                                    })()}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

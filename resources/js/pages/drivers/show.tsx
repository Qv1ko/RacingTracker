import { ActivityChart } from '@/components/charts/activity-chart';
import { PositionsChart } from '@/components/charts/positions-chart';
import { SinglePointsChart } from '@/components/charts/single-points-chart';
import { Icon } from '@/components/icon';
import InfoGrid from '@/components/info-grid';
import StatCard from '@/components/stat-card';
import FlagIcon from '@/components/ui/flag-icon';
import AppLayout from '@/layouts/app-layout';
import { HelmetIconNode } from '@/lib/utils';
import { Driver, type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Trophy, Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Driver profile',
        href: '/drivers',
    },
];

export default function Drivers({ driver }: { driver: Driver }) {
    console.log(driver);
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
                        <StatCard mainValue={driver.seasons || 0} label="Seasons active" />
                        <StatCard mainValue={driver.championshipsCount || 0} subValue={driver.seasons} label="Championships" />
                        <StatCard mainValue={driver.races || 0} label="Races" />
                        <StatCard mainValue={driver.wins || 0} subValue={driver.races} label="Wins" />
                        <StatCard mainValue={driver.points?.toFixed(3) || 0} label="Points" />
                        <StatCard mainValue={driver.maxPoints?.toFixed(3) || 0} label="Max points" />
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
                    {driver.info && (
                        <div>
                            <InfoGrid
                                data={[
                                    {
                                        key: 'First race',
                                        value: driver.info.firstRace && (
                                            <Link
                                                href={`/races/${driver.info.firstRace.id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >{`${driver.info.firstRace.name} (${new Date(driver.info.firstRace.date).toLocaleDateString('en-GB')})`}</Link>
                                        ),
                                    },
                                    {
                                        key: 'Last race',
                                        value: driver.info.lastRace && (
                                            <Link href={`/races/${driver.info.lastRace.id}`} className="hover:text-primary flex items-center gap-2">
                                                {`${driver.info.lastRace.name} (${new Date(driver.info.lastRace.date).toLocaleDateString('en-GB')})`}
                                            </Link>
                                        ),
                                    },
                                    {
                                        key: 'First win',
                                        value: driver.info.firstWin && (
                                            <Link
                                                href={`/races/${driver.info.firstWin.id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >{`${driver.info.firstWin.name} (${new Date(driver.info.firstWin.date).toLocaleDateString('en-GB')})`}</Link>
                                        ),
                                    },
                                    {
                                        key: 'Last win',
                                        value: driver.info.lastWin && (
                                            <Link
                                                href={`/races/${driver.info.lastWin.id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >{`${driver.info.lastWin.name} (${new Date(driver.info.lastWin.date).toLocaleDateString('en-GB')})`}</Link>
                                        ),
                                    },
                                    {
                                        key: 'Wins',
                                        value: <>{driver.wins || 0}</>,
                                    },
                                    {
                                        key: 'Win percentage',
                                        value: <>{driver.info.winPercentage || 0}%</>,
                                    },
                                    {
                                        key: 'Podiums',
                                        value: <>{driver.info.podiums || 0}</>,
                                    },
                                    {
                                        key: 'Podium percentage',
                                        value: <>{driver.info.podiumPercentage || 0}%</>,
                                    },
                                    {
                                        key: 'Without position',
                                        value: driver.info.withoutPosition > 0 && <>{driver.info.withoutPosition}</>,
                                    },
                                    {
                                        key: 'Championships',
                                        value: driver.info.championships && (
                                            <div className="flex flex-wrap gap-2">
                                                {driver.info.championships.map((championship) => (
                                                    <Link key={championship} href={`/seasons/${championship}`} className="hover:text-primary">
                                                        <span className="flex items-center gap-1">
                                                            <Trophy stroke="black" fill="gold" size={16} /> {championship}
                                                        </span>
                                                    </Link>
                                                ))}
                                            </div>
                                        ),
                                    },
                                ]}
                            />
                        </div>
                    )}
                    {driver.pointsHistory && <SinglePointsChart data={driver.pointsHistory} />}
                    <table key="seasons"></table>
                    {driver.positionsHistory && <PositionsChart data={driver.positionsHistory} />}
                    {((driver.teams?.length || 0) > 0 || driver.teammates) && (
                        <div className="grid auto-rows-min grid-cols-1 gap-4 md:grid-cols-2">
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

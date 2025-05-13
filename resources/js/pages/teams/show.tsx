import { PositionsChart } from '@/components/charts/positions-chart';
import { SinglePointsChart } from '@/components/charts/single-points-chart';
import { Icon } from '@/components/icon';
import InfoGrid from '@/components/info-grid';
import StatCard from '@/components/stat-card';
import FlagIcon from '@/components/ui/flag-icon';
import AppLayout from '@/layouts/app-layout';
import { HelmetIconNode } from '@/lib/utils';
import { Team, type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Trophy, Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Team profile',
        href: '/teams',
    },
];

export default function Teams({ team }: { team: Team }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={team.name} />
            <div className="px-4 py-8">
                <div className="flex justify-center">
                    <div className="mb-8 flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:justify-start">
                        <div className="flex h-16 w-16 items-center justify-center">
                            <Users className="h-12 w-12" />
                        </div>
                        <div className="text-center sm:text-left">
                            <h2 className="text-2xl font-bold">{team.name}</h2>
                            {team.nationality && (
                                <div className="flex items-center gap-2">
                                    <FlagIcon nationality={team.nationality.toString()} size={24} />
                                    <p>{team.nationality}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
                <div className="flex h-full flex-1 flex-col gap-4 rounded-sm">
                    <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                        <StatCard mainValue={team.seasons || 0} label="Seasons active" />
                        <StatCard mainValue={team.championshipsCount || 0} subValue={team.seasons} label="Championships" />
                        <StatCard mainValue={team.races || 0} label="Races" />
                        <StatCard mainValue={team.wins || 0} subValue={team.races} label="Wins" />
                        <StatCard mainValue={team.points?.toFixed(3) || 0} label="Points" />
                        <StatCard mainValue={team.maxPoints?.toFixed(3) || 0} label="Max points" />
                    </div>
                    {team.info && (
                        <div>
                            <InfoGrid
                                data={[
                                    {
                                        key: 'First race',
                                        value: team.info.firstRace && (
                                            <Link
                                                href={`/races/${team.info.firstRace.id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >{`${team.info.firstRace.name} (${new Date(team.info.firstRace.date).toLocaleDateString('en-GB')})`}</Link>
                                        ),
                                    },
                                    {
                                        key: 'Last race',
                                        value: team.info.lastRace && (
                                            <Link href={`/races/${team.info.lastRace.id}`} className="hover:text-primary flex items-center gap-2">
                                                {`${team.info.lastRace.name} (${new Date(team.info.lastRace.date).toLocaleDateString('en-GB')})`}
                                            </Link>
                                        ),
                                    },
                                    {
                                        key: 'First win',
                                        value: team.info.firstWin && (
                                            <Link
                                                href={`/races/${team.info.firstWin.id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >{`${team.info.firstWin.name} (${new Date(team.info.firstWin.date).toLocaleDateString('en-GB')})`}</Link>
                                        ),
                                    },
                                    {
                                        key: 'Last win',
                                        value: team.info.lastWin && (
                                            <Link
                                                href={`/races/${team.info.lastWin.id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >{`${team.info.lastWin.name} (${new Date(team.info.lastWin.date).toLocaleDateString('en-GB')})`}</Link>
                                        ),
                                    },
                                    {
                                        key: 'Wins',
                                        value: <>{team.wins || 0}</>,
                                    },
                                    {
                                        key: 'Win percentage',
                                        value: <>{team.info.winPercentage || 0}%</>,
                                    },
                                    {
                                        key: 'Podiums',
                                        value: <>{team.info.podiums || 0}</>,
                                    },
                                    {
                                        key: 'Podium percentage',
                                        value: <>{team.info.podiumPercentage || 0}%</>,
                                    },
                                    {
                                        key: 'Without position',
                                        value: team.info.withoutPosition > 0 && <>{team.info.withoutPosition}</>,
                                    },
                                    {
                                        key: 'Teams ranking position',
                                        value: team.info.raking && <>{team.info.raking.position}</>,
                                    },
                                    {
                                        key: 'Championships',
                                        value: team.info.championships && (
                                            <div className="flex flex-wrap gap-2">
                                                {team.info.championships.map((championship) => (
                                                    <Link key={championship} href={`/seasons/${championship}`} className="hover:text-primary">
                                                        <span className="flex items-center gap-1">
                                                            <Trophy stroke="gold" fill="gold" size={16} /> {championship}
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
                    {team.pointsHistory && <SinglePointsChart data={team.pointsHistory} />}
                    <table key="seasons"></table>
                    {team.positionsHistory && <PositionsChart data={team.positionsHistory} />}
                    {(team.drivers?.length || 0) > 0 && (
                        <div className="grid auto-rows-min justify-items-center gap-4">
                            <div className="flex items-center justify-center gap-2">
                                <div className="flex h-12 w-12 items-center justify-center rounded-full">
                                    <Icon iconNode={HelmetIconNode} className="h-8 w-8" />
                                </div>
                                <h3 className="text-xl font-semibold">Drivers</h3>
                            </div>
                            {team.drivers &&
                                (() => {
                                    const drivers = team.drivers.filter((team) => team);
                                    return (
                                        <div className={'grid w-full grid-cols-[repeat(auto-fit,_minmax(150px,_max-content))] justify-center gap-4'}>
                                            {drivers.map(
                                                (driver, index) =>
                                                    driver && (
                                                        <div key={`driver-${index}`} className="w-auto rounded-sm border px-4 py-2">
                                                            <Link
                                                                href={`/drivers/${driver.id}`}
                                                                className="hover:text-primary flex items-center justify-center gap-2"
                                                            >
                                                                <FlagIcon nationality={driver.nationality || 'unknown'} size={16} />{' '}
                                                                {driver.name[0].toUpperCase()}. {driver.surname}
                                                            </Link>
                                                        </div>
                                                    ),
                                            )}
                                        </div>
                                    );
                                })()}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

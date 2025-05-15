import { MultiPointsChart } from '@/components/charts/multi-points-chart';
import { DataTable } from '@/components/data-table';
import { columns as driverRankingColumns } from '@/components/drivers/ranking-column';
import { SelectSeason } from '@/components/select-season';
import { columns as teamRankingColumns } from '@/components/teams/ranking-column';
import { columns as teamStandingsColumns } from '@/components/teams/team-standings-columns';
import { Icon } from '@/components/ui/icon';
import AppLayout from '@/layouts/app-layout';
import { HelmetIconNode } from '@/lib/utils';
import { Driver, Team, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/',
    },
];

export default function Home({
    seasons,
    season,
    drivers,
    teams,
}: {
    seasons: string[];
    season: {
        season: string;
        driversPoints: { driver: Driver; pointsHistory: { race: string; date: string; points: number }[] }[];
        teamStandings: { position: string; team: Team; points: number; gap: number }[];
    };
    drivers: {
        ranking: { position: number; driver: Driver; points: number }[];
    };
    teams: {
        ranking: { position: number; team: Team; points: number }[];
    };
}) {
    const driversPointsData = season.driversPoints.flatMap(({ driver: { id }, pointsHistory }) =>
        pointsHistory.map(({ race, date, points }) => ({ race, date, id, points })),
    );

    const driversPointsChartData = Object.values(
        driversPointsData.reduce<
            Record<
                string,
                {
                    race: string;
                    date: string;
                    [id: string]: number | string;
                }
            >
        >((acc, { race, date, id, points }) => {
            if (!acc[race]) {
                acc[race] = { race, date };
            }
            acc[race][id] = points;
            return acc;
        }, {}),
    ).sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime());

    const driversPointsChartConfig = season.driversPoints.reduce<Record<string, { label: string }>>((acc, { driver: { id, name, surname } }) => {
        acc[id] = { label: `${name[0].toUpperCase()}. ${surname}` };
        return acc;
    }, {});

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-sm px-4 py-8">
                <div className="flex justify-between">
                    <SelectSeason all={false} seasons={seasons} selectedValue={season.season ? season.season.toString() : ''} url={''} />
                </div>
                <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div className={`${season.teamStandings && season.teamStandings.length > 0 ? 'lg:col-span-2' : 'lg:col-span-3'}`}>
                        <MultiPointsChart title="" data={driversPointsChartData} chartConfig={driversPointsChartConfig} />
                    </div>
                    {season.teamStandings && season.teamStandings.length > 0 && (
                        <div>
                            <DataTable columns={teamStandingsColumns} data={season.teamStandings} />
                        </div>
                    )}
                </div>
                <div>
                    <div className="flex items-center justify-center gap-2">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full">
                            <Icon iconNode={HelmetIconNode} className="h-8 w-8" />
                        </div>
                        <h3 className="text-xl font-semibold">Driver history</h3>
                    </div>
                    <DataTable columns={driverRankingColumns} data={drivers.ranking} />
                </div>
                <div>
                    <div className="flex items-center justify-center gap-2">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full">
                            <Users className="h-8 w-8" />
                        </div>
                        <h3 className="text-xl font-semibold">Team history</h3>
                    </div>
                    <DataTable columns={teamRankingColumns} data={teams.ranking} />
                </div>
            </div>
        </AppLayout>
    );
}

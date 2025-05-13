import { MultiPointsChart } from '@/components/charts/multi-points-chart';
import { DataTable } from '@/components/data-table';
import { SelectSeason } from '@/components/select-season';
import { columns as teamStandingsColumns } from '@/components/teams/team-standings-columns';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { Driver, Team, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/',
    },
];

export default function Home({
    seasons,
    season,
}: {
    seasons: string[];
    season: {
        season: string;
        driversPoints: { driver: Driver; pointsHistory: { race: string; date: string; points: number }[] }[];
        teamStandings: { position: string; team: Team; points: number; gap: number }[];
    };
}) {
    console.log(season);

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
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-sm border md:min-h-min">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </AppLayout>
    );
}

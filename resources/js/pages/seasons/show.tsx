import { MultiPointsChart } from '@/components/charts/multi-points-chart';
import { DataTable } from '@/components/data-table';
import { columns as driverStandingsColumns } from '@/components/drivers/driver-standings-columns';
import { Icon } from '@/components/icon';
import { columns as teamStandingsColumns } from '@/components/teams/team-standings-columns';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { HelmetIconNode } from '@/lib/utils';
import { Season, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { CalendarFold, Flag, Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Season profile',
        href: '/seasons',
    },
];

export default function Seasons({ season }: { season: Season }) {
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

    const teamsPointsData = season.teamsPoints?.flatMap(({ team: { id }, pointsHistory }) =>
        pointsHistory.map(({ race, date, points }) => ({ race, date, id, points })),
    );

    const teamsPointsChartData = teamsPointsData
        ? Object.values(
              teamsPointsData.reduce<
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
          ).sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
        : [];

    const teamsPointsChartConfig = season.teamsPoints
        ? season.teamsPoints.reduce<Record<string, { label: string }>>((acc, { team: { id, name } }) => {
              acc[id] = { label: name };
              return acc;
          }, {})
        : {};

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={'Season ' + season.season} />
            <div className="px-4 py-8">
                <div className="flex justify-center">
                    <div className="mb-8 flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:justify-start">
                        <div className="flex h-16 w-16 items-center justify-center">
                            <CalendarFold className="h-12 w-12" />
                        </div>
                        <div className="text-center sm:text-left">
                            <h2 className="text-2xl font-bold">Season {season.season}</h2>
                        </div>
                    </div>
                </div>
                <div className="flex h-full flex-1 flex-col gap-4 rounded-sm">
                    <div className="grid auto-rows-min gap-4 md:grid-cols-2">
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-sm border">
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        </div>
                    </div>
                    <div className="flex items-center justify-center gap-2">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full">
                            <Icon iconNode={HelmetIconNode} className="h-8 w-8" />
                        </div>
                        <h3 className="text-xl font-semibold">Drivers</h3>
                    </div>
                    {season.driverStandings && <DataTable columns={driverStandingsColumns} data={season.driverStandings} />}
                    <MultiPointsChart title="Drivers points" data={driversPointsChartData} chartConfig={driversPointsChartConfig} />
                    <div className="flex items-center justify-center gap-2">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full">
                            <Users className="h-8 w-8" />
                        </div>
                        <h3 className="text-xl font-semibold">Teams</h3>
                    </div>
                    {season.teamStandings && <DataTable columns={teamStandingsColumns} data={season.teamStandings} />}
                    {season.teamsPoints && <MultiPointsChart title="Teams points" data={teamsPointsChartData} chartConfig={teamsPointsChartConfig} />}
                    <div className="flex items-center justify-center gap-2">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full">
                            <Flag className="h-8 w-8" />
                        </div>
                        <h3 className="text-xl font-semibold">Races</h3>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

import { MultiPointsChart } from '@/components/charts/multi-points-chart';
import { DataTable } from '@/components/data-table';
import { columns as driverStandingsColumns } from '@/components/drivers/driver-standings-columns';
import { Icon } from '@/components/icon';
import InfoGrid from '@/components/info-grid';
import { columns as racesColumns } from '@/components/seasons/races-columns';
import { columns as teamStandingsColumns } from '@/components/teams/team-standings-columns';
import FlagIcon from '@/components/ui/flag-icon';
import AppLayout from '@/layouts/app-layout';
import { HelmetIconNode } from '@/lib/utils';
import { Season, type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
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
                    <InfoGrid
                        data={[
                            {
                                key: 'First race',
                                value: season.info.firstRace && (
                                    <Link
                                        href={`/races/${season.info.firstRace.id}`}
                                        className="hover:text-primary flex items-center gap-2"
                                    >{`${season.info.firstRace.name} (${new Date(season.info.firstRace.date).toLocaleDateString('en-GB')})`}</Link>
                                ),
                            },
                            {
                                key: 'Last race',
                                value: season.info.lastRace && (
                                    <Link href={`/races/${season.info.lastRace.id}`} className="hover:text-primary flex items-center gap-2">
                                        {`${season.info.lastRace.name} (${new Date(season.info.lastRace.date).toLocaleDateString('en-GB')})`}
                                    </Link>
                                ),
                            },
                            {
                                key: 'Champion driver',
                                value: season.info.championDriver && (
                                    <Link
                                        key={season.info.championDriver.id}
                                        href={`/drivers/${season.info.championDriver.id}`}
                                        className="hover:text-primary flex items-center gap-2"
                                    >
                                        <FlagIcon
                                            nationality={season.info.championDriver.nationality ? season.info.championDriver.nationality : 'unkown'}
                                            size={16}
                                        />{' '}
                                        {`${season.info.championDriver.name[0].toUpperCase()}. ${season.info.championDriver.surname}`}
                                    </Link>
                                ),
                            },
                            {
                                key: 'Champion team',
                                value: season.info.championTeam && (
                                    <Link
                                        key={season.info.championTeam.id}
                                        href={`/drivers/${season.info.championTeam.id}`}
                                        className="hover:text-primary flex items-center gap-2"
                                    >
                                        <FlagIcon
                                            nationality={season.info.championTeam.nationality ? season.info.championTeam.nationality : 'unkown'}
                                            size={16}
                                        />{' '}
                                        {`${season.info.championTeam.name}`}
                                    </Link>
                                ),
                            },
                            {
                                key: 'Races',
                                value: season.info.racesCount && <>{season.info.racesCount || 0}</>,
                            },
                            {
                                key: 'Most wins',
                                value: season.info.mostWins && (
                                    <div className="flex flex-wrap gap-2">
                                        {season.info.mostWins.map((item) => (
                                            <Link
                                                key={item.driver_id}
                                                href={`/drivers/${item.driver_id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >
                                                <FlagIcon nationality={item.driver.nationality ? item.driver.nationality : 'unkown'} size={16} />{' '}
                                                {`${item.driver.name[0].toUpperCase()}. ${item.driver.surname}`}
                                            </Link>
                                        ))}
                                        <>({season.info.mostWins[0].wins})</>
                                    </div>
                                ),
                            },
                            {
                                key: 'Most podiums',
                                value: season.info.mostPodiums && season.info.mostPodiums.length > 0 && (
                                    <div className="flex flex-wrap gap-2">
                                        {season.info.mostPodiums.map((item) => (
                                            <Link
                                                key={item.driver_id}
                                                href={`/drivers/${item.driver_id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >
                                                <FlagIcon nationality={item.driver.nationality ? item.driver.nationality : 'unkown'} size={16} />{' '}
                                                {`${item.driver.name[0].toUpperCase()}. ${item.driver.surname}`}
                                            </Link>
                                        ))}
                                        <>({season.info.mostPodiums[0].podiums})</>
                                    </div>
                                ),
                            },
                            {
                                key: 'Most without position',
                                value: season.info.mostWithoutPosition && season.info.mostWithoutPosition.length > 0 && (
                                    <div className="flex flex-wrap gap-2">
                                        {season.info.mostWithoutPosition.map((item) => (
                                            <Link
                                                key={item.driver_id}
                                                href={`/drivers/${item.driver_id}`}
                                                className="hover:text-primary flex items-center gap-2"
                                            >
                                                <FlagIcon nationality={item.driver.nationality ? item.driver.nationality : 'unkown'} size={16} />{' '}
                                                {`${item.driver.name[0].toUpperCase()}. ${item.driver.surname}`}
                                            </Link>
                                        ))}
                                        <>({season.info.mostWithoutPosition[0].withoutPosition})</>
                                    </div>
                                ),
                            },
                        ]}
                    />
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
                    <DataTable columns={racesColumns} data={season.races} />
                </div>
            </div>
        </AppLayout>
    );
}

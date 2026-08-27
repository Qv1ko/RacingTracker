import { MultiPointsChart } from "@/components/charts/multi-points-chart";
import { WinDistributionChart } from "@/components/charts/win-distribution-chart";
import { DataTable } from "@/components/data-table";
import { EmptyState } from "@/components/empty-state";
import { columns as teamStandingsColumns } from "@/components/teams/team-standings-columns";
import AppLayout from "@/layouts/app-layout";
import { Driver, Team, type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/react";
import { ChartNoAxesCombined } from "lucide-react";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Home",
        href: "/",
    },
];

export default function Home({
    season,
}: {
    season: {
        season: string;
        driversPoints: {
            driver: Driver;
            pointsHistory: { race: string; date: string; points: number }[];
        }[];
        teamStandings: { position: string; team: Team; points: number; gap: number }[];
        teamWins: { id: number; name: string; color: string; count: number }[];
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

    const driversPointsChartConfig = season.driversPoints.reduce<
        Record<string, { label: string; color?: string }>
    >((acc, { driver: { id, name, surname, color } }) => {
        acc[id] = { label: `${name[0].toUpperCase()}. ${surname}`, color };
        return acc;
    }, {});

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-sm px-4 py-8">
                <div className="flex justify-between"></div>
                <div className="grid grid-cols-1 gap-4">
                    <div>
                        {driversPointsChartData.length > 0 ? (
                            <MultiPointsChart
                                title=""
                                data={driversPointsChartData}
                                chartConfig={driversPointsChartConfig}
                            />
                        ) : (
                            <EmptyState
                                icon={<ChartNoAxesCombined aria-hidden="true" />}
                                title="No points data"
                                description="Driver points will appear here once races have been recorded."
                            />
                        )}
                    </div>
                    <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-stretch">
                        {season.teamStandings && (
                            <DataTable columns={teamStandingsColumns} data={season.teamStandings} />
                        )}
                        {season.teamWins.length > 0 ? (
                            <WinDistributionChart data={season.teamWins} />
                        ) : (
                            <EmptyState
                                title="No team wins data"
                                description="Team wins will appear here once race results are available."
                            />
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

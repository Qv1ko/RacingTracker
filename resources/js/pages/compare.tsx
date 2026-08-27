import { MultiPointsChart } from "@/components/charts/multi-points-chart";
import StatCard from "@/components/stat-card";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import AppLayout from "@/layouts/app-layout";
import { formatNumber } from "@/lib/utils";
import { Driver, DriverComparison, type BreadcrumbItem } from "@/types";
import { Head, router } from "@inertiajs/react";
import { GitCompareArrows } from "lucide-react";

const breadcrumbs: BreadcrumbItem[] = [{ title: "Compare", href: "/compare" }];

export default function Compare({
    seasons,
    season,
    drivers,
    selected,
    comparison,
}: {
    seasons: string[];
    season: string | null;
    drivers: Driver[];
    selected: { driver1: number | null; driver2: number | null };
    comparison: DriverComparison[];
}) {
    const update = (key: "driver1" | "driver2", value: string) => {
        const next = {
            season: season ?? seasons[0] ?? "",
            driver1: key === "driver1" ? value : (selected.driver1?.toString() ?? ""),
            driver2: key === "driver2" ? value : (selected.driver2?.toString() ?? ""),
        };
        router.get("/compare", next, { preserveScroll: true });
    };

    const chartData = comparison
        .flatMap(({ driver, pointsHistory }) =>
            pointsHistory.map(({ race, date, points }) => ({ race, date, id: driver.id, points })),
        )
        .reduce<Record<string, { race: string; date: string; [id: string]: number | string }>>(
            (data, point) => {
                data[point.race] ??= { race: point.race, date: point.date };
                data[point.race][point.id] = point.points;
                return data;
            },
            {},
        );
    const chartConfig = Object.fromEntries(
        comparison.map(({ driver }) => [
            driver.id,
            { label: `${driver.name[0]}. ${driver.surname}`, color: driver.color },
        ]),
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Compare drivers" />
            <div className="container mx-auto px-4 py-8">
                <div className="mb-4 flex items-center justify-center gap-2">
                    <div className="flex h-12 w-12 items-center justify-center rounded-full">
                        <GitCompareArrows className="h-8 w-8" />
                    </div>
                    <h3 className="text-xl font-semibold">Head to head</h3>
                </div>
                <div className="mb-6 grid gap-4 md:grid-cols-2">
                    <Selector
                        label="Driver 1"
                        value={selected.driver1?.toString() ?? ""}
                        onValueChange={(value) => update("driver1", value)}
                    >
                        {drivers.map((driver) => (
                            <SelectItem key={driver.id} value={driver.id.toString()}>
                                {driver.name} {driver.surname}
                            </SelectItem>
                        ))}
                    </Selector>
                    <Selector
                        label="Driver 2"
                        value={selected.driver2?.toString() ?? ""}
                        onValueChange={(value) => update("driver2", value)}
                    >
                        {drivers.map((driver) => (
                            <SelectItem key={driver.id} value={driver.id.toString()}>
                                {driver.name} {driver.surname}
                            </SelectItem>
                        ))}
                    </Selector>
                </div>
                {comparison.length === 2 ? (
                    <>
                        <MultiPointsChart
                            title={`${comparison[0].driver.surname} vs ${comparison[1].driver.surname} points`}
                            data={Object.values(chartData).sort((a, b) =>
                                a.date.localeCompare(b.date),
                            )}
                            chartConfig={chartConfig}
                        />
                        <div className="grid gap-4 md:grid-cols-2">
                            {comparison.map(({ driver, summary }) => (
                                <div key={driver.id} className="rounded-sm border p-4">
                                    <h2 className="mb-4 text-center text-lg font-semibold">
                                        {driver.name} {driver.surname}
                                    </h2>
                                    <div className="grid grid-cols-2 gap-3">
                                        <StatCard
                                            mainValue={formatNumber(summary.points)}
                                            label="Points"
                                        />
                                        <StatCard mainValue={summary.races} label="Races" />
                                        <StatCard mainValue={summary.wins} label="Wins" />
                                        <StatCard mainValue={summary.podiums} label="Podiums" />
                                        <StatCard
                                            mainValue={
                                                summary.averageFinish === null
                                                    ? "-"
                                                    : formatNumber(summary.averageFinish)
                                            }
                                            label="Average finish"
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </>
                ) : (
                    <div className="rounded-sm border border-dashed p-12 text-center">
                        <p className="font-medium">Select two drivers to compare.</p>
                        <p className="text-muted-foreground mt-1 text-sm">
                            This season has {drivers.length} participating driver
                            {drivers.length === 1 ? "" : "s"}.
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function Selector({
    label,
    children,
    ...props
}: {
    label: string;
    value: string;
    onValueChange: (value: string) => void;
    children: React.ReactNode;
}) {
    return (
        <label className="space-y-2 text-sm font-medium">
            <span>{label}</span>
            <Select {...props}>
                <SelectTrigger>
                    <SelectValue placeholder={`Select ${label.toLowerCase()}`} />
                </SelectTrigger>
                <SelectContent>{children}</SelectContent>
            </Select>
        </label>
    );
}

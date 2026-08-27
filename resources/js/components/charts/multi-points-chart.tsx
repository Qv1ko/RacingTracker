import { useMemo } from "react";

import { CartesianGrid, Line, LineChart, Tooltip, XAxis, YAxis } from "recharts";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ChartConfig, ChartContainer, ChartTooltipContent } from "@/components/ui/chart";
import { downsample } from "@/lib/downsample";

type MultiPointsChartProps = {
    title?: string;
    data: {
        race: string;
        date: string;
        [id: string]: number | string;
    }[];
    chartConfig: ChartConfig;
};

type MultiPointsChart = {
    title?: string;
    data: {
        race: string;
        date: string;
        [id: string]: number | string;
    }[];
    chartConfig: ChartConfig;
};

export const MultiPointsChart: React.FC<MultiPointsChartProps> = ({
    title = "Season points",
    data,
    chartConfig,
}) => {
    const sortedData = useMemo(() => {
        const mapped = data.map((race) => {
            const { race: raceName, date, ...driverPoints } = race;
            const sortedEntries = Object.entries(driverPoints)
                .filter(([, v]) => typeof v === "number")
                .sort(([, a], [, b]) => (b as number) - (a as number));
            const sortedRace: Record<string, string | number> = {};
            sortedEntries.forEach(([id, pts]) => {
                sortedRace[id] = pts;
            });
            sortedRace.race = raceName;
            sortedRace.date = date;
            return sortedRace;
        });

        return downsample(mapped);
    }, [data]);

    const driverKeys = Array.from(
        sortedData.reduce<Set<string>>((keys, obj) => {
            Object.keys(obj)
                .filter((k) => k !== "race" && k !== "date")
                .forEach((k) => keys.add(k));
            return keys;
        }, new Set<string>()),
    );

    return (
        <Card className="mb-4">
            <CardHeader>
                <CardTitle className="text-center">{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <ChartContainer config={chartConfig} className="h-[360px] w-full">
                    <LineChart data={sortedData} margin={{ top: 12, left: -22 }}>
                        <CartesianGrid vertical={false} />
                        <XAxis
                            dataKey="race"
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            tickFormatter={(val) =>
                                typeof val === "string" ? val.slice(0, 3) : String(val)
                            }
                        />
                        <YAxis
                            domain={([dataMin, dataMax]) => [dataMin - 1, dataMax + 1]}
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            tickFormatter={(value) => value.toFixed(0).toString()}
                        />
                        <Tooltip
                            isAnimationActive={false}
                            cursor={{
                                stroke: "var(--muted-foreground)",
                                strokeDasharray: "3 3",
                                strokeWidth: 1,
                            }}
                            content={({ payload = [], label, active, coordinate, offset }) => {
                                const sorted = payload
                                    .slice()
                                    .sort((a, b) => Number(b.value ?? 0) - Number(a.value ?? 0));
                                const formatted = sorted.map((entry) => ({
                                    ...entry,
                                    value: Number(entry.value).toLocaleString("en-US", {
                                        maximumFractionDigits: 3,
                                        useGrouping: false,
                                    }),
                                }));

                                const point = payload[0]?.payload as
                                    | { race?: string; date?: string }
                                    | undefined;
                                const raceDate = point?.date
                                    ? new Date(point.date).toLocaleDateString("en-GB")
                                    : "";

                                return (
                                    <ChartTooltipContent
                                        active={active}
                                        payload={formatted}
                                        label={`${point?.race ?? label} (${raceDate})`}
                                        coordinate={coordinate}
                                        offset={offset}
                                    />
                                );
                            }}
                            wrapperStyle={{ zIndex: 9999, pointerEvents: "none" }}
                        />
                        {driverKeys.map((key, i) => {
                            const configColor = (chartConfig[key] as { color?: string } | undefined)
                                ?.color;
                            const lineColor = configColor ?? `var(--chart-${(i % 5) + 1})`;

                            return (
                                <Line
                                    key={key}
                                    dataKey={key}
                                    type="linear"
                                    stroke={lineColor}
                                    strokeWidth={2}
                                    dot={false}
                                    activeDot={{
                                        r: 5,
                                        strokeWidth: 2,
                                        fill: lineColor,
                                    }}
                                    connectNulls
                                    isAnimationActive={false}
                                />
                            );
                        })}
                    </LineChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
};

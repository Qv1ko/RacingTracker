import { useMemo } from "react";

import { CartesianGrid, Line, LineChart, Tooltip, XAxis, YAxis } from "recharts";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ChartConfig, ChartContainer, ChartTooltipContent } from "@/components/ui/chart";
import { downsample } from "@/lib/downsample";

const chartConfig = {
    points: {
        label: "Points",
    },
} satisfies ChartConfig;

type SinglePointsChartProps = {
    title?: string;
    data: {
        race: string;
        date: string;
        points: number;
    }[];
};

export const SinglePointsChart: React.FC<SinglePointsChartProps> = ({
    title = "Points history",
    data,
}) => {
    const points = useMemo(() => downsample(data), [data]);

    return (
        <Card className="mb-4">
            <CardHeader>
                <CardTitle className="text-center">{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <ChartContainer config={chartConfig} className="h-[360px] w-full">
                    <LineChart accessibilityLayer data={points} margin={{ top: 12, left: -22 }}>
                        <CartesianGrid vertical={false} />
                        <XAxis
                            dataKey="race"
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            tickFormatter={(value) => value.slice(0, 3)}
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
                            cursor={{ strokeDasharray: "3 3" }}
                            content={({ payload = [], label, active, coordinate, offset }) => {
                                const point = payload[0]?.payload as
                                    | { race?: string; date?: string }
                                    | undefined;
                                const raceDate = point?.date
                                    ? new Date(point.date).toLocaleDateString("en-GB")
                                    : "";

                                return (
                                    <ChartTooltipContent
                                        active={active}
                                        payload={payload}
                                        label={`${point?.race ?? label} (${raceDate})`}
                                        coordinate={coordinate}
                                        offset={offset}
                                    />
                                );
                            }}
                            wrapperStyle={{ zIndex: 9999, pointerEvents: "none" }}
                        />

                        <Line
                            dataKey="points"
                            type="linear"
                            stroke="var(--color-primary)"
                            strokeWidth={2}
                            dot={false}
                            isAnimationActive={false}
                        />
                    </LineChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
};

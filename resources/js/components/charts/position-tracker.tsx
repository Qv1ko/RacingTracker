import { useState } from "react";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    ChartConfig,
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
} from "@/components/ui/chart";
import { Checkbox } from "@/components/ui/checkbox";
import { CartesianGrid, Line, LineChart, XAxis, YAxis } from "recharts";

type PositionDriver = {
    id: number;
    key: string;
    name: string;
    color?: string | null;
};

type PositionTrackerProps = {
    drivers: PositionDriver[];
    data: { race: string; date: string; [key: string]: string | number | null }[];
    title?: string;
};

const DEFAULT_DRIVER_COUNT = 8;

export function PositionTracker({
    drivers,
    data,
    title = "Race position tracker",
}: PositionTrackerProps) {
    const [selectedKeys, setSelectedKeys] = useState<string[]>(() =>
        drivers.slice(0, DEFAULT_DRIVER_COUNT).map(({ key }) => key),
    );

    const chartConfig = drivers.reduce<ChartConfig>((config, driver) => {
        config[driver.key] = { label: driver.name, color: driver.color ?? undefined };
        return config;
    }, {});

    const toggleDriver = (key: string) => {
        setSelectedKeys((current) =>
            current.includes(key) ? current.filter((item) => item !== key) : [...current, key],
        );
    };

    return (
        <Card className="mb-4">
            <CardHeader>
                <CardTitle className="text-center">{title}</CardTitle>
                <div className="flex flex-wrap justify-center gap-x-4 gap-y-2 pt-2">
                    {drivers.map((driver) => (
                        <label
                            key={driver.key}
                            className="text-muted-foreground flex items-center gap-2 text-xs"
                        >
                            <Checkbox
                                checked={selectedKeys.includes(driver.key)}
                                onCheckedChange={() => toggleDriver(driver.key)}
                            />
                            {driver.name}
                        </label>
                    ))}
                </div>
            </CardHeader>
            <CardContent>
                <ChartContainer config={chartConfig} className="h-[400px] w-full">
                    <LineChart data={data} margin={{ top: 12, right: 12, left: -22 }}>
                        <CartesianGrid vertical={false} />
                        <XAxis
                            dataKey="race"
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            tickFormatter={(value) => String(value).slice(0, 3)}
                        />
                        <YAxis
                            reversed
                            domain={[1, "dataMax"]}
                            allowDecimals={false}
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                        />
                        <ChartTooltip
                            isAnimationActive={false}
                            wrapperStyle={{ zIndex: 9999, pointerEvents: "none" }}
                            content={({ payload = [], label, active, coordinate, offset }) => {
                                const point = payload[0]?.payload as
                                    | { race?: string; date?: string }
                                    | undefined;
                                const raceDate = point?.date
                                    ? new Date(point.date).toLocaleDateString("en-GB")
                                    : "";
                                const entries = payload
                                    .filter((entry) => typeof entry.value === "number")
                                    .sort((a, b) => Number(a.value) - Number(b.value));

                                return (
                                    <ChartTooltipContent
                                        active={active}
                                        payload={entries}
                                        label={`${point?.race ?? label} (${raceDate})`}
                                        coordinate={coordinate}
                                        offset={offset}
                                    />
                                );
                            }}
                        />
                        <ChartLegend content={<ChartLegendContent />} />
                        {drivers
                            .filter(({ key }) => selectedKeys.includes(key))
                            .map((driver) => (
                                <Line
                                    key={driver.key}
                                    dataKey={driver.key}
                                    name={driver.name}
                                    type="linear"
                                    stroke={driver.color ?? undefined}
                                    strokeWidth={2}
                                    dot={false}
                                    activeDot={{ r: 5, strokeWidth: 2 }}
                                    connectNulls={false}
                                    isAnimationActive={false}
                                />
                            ))}
                    </LineChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
}

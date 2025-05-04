import { CartesianGrid, Line, LineChart, Tooltip, XAxis, YAxis } from 'recharts';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartConfig, ChartContainer, ChartTooltipContent } from '@/components/ui/chart';

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

export const MultiPointsChart: React.FC<MultiPointsChartProps> = ({ title = 'Season points', data, chartConfig }) => {
    const sortedData = data.map((race) => {
        const { race: raceName, date, ...driverPoints } = race;
        const sortedEntries = Object.entries(driverPoints)
            .filter(([, v]) => typeof v === 'number')
            .sort(([, a], [, b]) => (b as number) - (a as number));
        const sortedRace: Record<string, string | number> = {};
        sortedEntries.forEach(([id, pts]) => {
            sortedRace[id] = pts;
        });
        sortedRace.race = raceName;
        sortedRace.date = date;
        return sortedRace;
    });

    const driverKeys = Array.from(
        sortedData.reduce<Set<string>>((keys, obj) => {
            Object.keys(obj)
                .filter((k) => k !== 'race' && k !== 'date')
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
                <ChartContainer config={chartConfig} className="h-[120px] w-full md:h-[240px] lg:h-[360px]">
                    <LineChart data={sortedData} margin={{ top: 16, right: 12, left: 12, bottom: 16 }}>
                        <CartesianGrid vertical={false} />
                        <XAxis
                            dataKey="race"
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            tickFormatter={(val) => (typeof val === 'string' ? val.slice(0, 3) : String(val))}
                        />
                        <YAxis domain={['dataMin', 'dataMax']} tickFormatter={(value) => value.toFixed(0).toString()} />
                        <Tooltip
                            cursor={{ strokeDasharray: '3 3' }}
                            content={({ payload, label, active, coordinate, offset }) => {
                                const sorted = (payload ?? []).slice().sort((a, b) => Number(b.value ?? 0) - Number(a.value ?? 0));
                                return <ChartTooltipContent active={active} payload={sorted} label={label} coordinate={coordinate} offset={offset} />;
                            }}
                        />
                        {driverKeys.map((key, i) => (
                            <Line
                                key={key}
                                dataKey={key}
                                type="linear"
                                stroke={`var(--chart-${(i % 5) + 1})`}
                                strokeWidth={2}
                                dot={false}
                                connectNulls
                                isAnimationActive={false}
                            />
                        ))}
                    </LineChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
};

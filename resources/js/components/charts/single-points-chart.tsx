import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartConfig, ChartContainer, ChartTooltipContent } from '@/components/ui/chart';
import { CartesianGrid, Line, LineChart, Tooltip, XAxis, YAxis } from 'recharts';

const chartConfig = {
    points: {
        label: 'Points',
    },
} satisfies ChartConfig;

type SinglePointsChartProps = {
    title?: string;
    data: {
        race: string;
        points: number;
    }[];
};

export const SinglePointsChart: React.FC<SinglePointsChartProps> = ({ title = 'Points history', data }) => {
    return (
        <Card className="mb-4">
            <CardHeader>
                <CardTitle className="text-center">{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <ChartContainer config={chartConfig} className="h-[360px] w-full">
                    <LineChart accessibilityLayer data={data} margin={{ top: 12, left: -22 }}>
                        <CartesianGrid vertical={false} />
                        <XAxis dataKey="race" tickLine={false} axisLine={false} tickMargin={8} tickFormatter={(value) => value.slice(0, 3)} />
                        <YAxis
                            domain={([dataMin, dataMax]) => [dataMin - 1, dataMax + 1]}
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            tickFormatter={(value) => value.toFixed(0).toString()}
                        />
                        <Tooltip
                            cursor={{ strokeDasharray: '3 3' }}
                            content={({ payload = [], label, active, coordinate, offset }) => {
                                const sorted = payload.slice().sort((a, b) => Number(b.value ?? 0) - Number(a.value ?? 0));
                                const formatted = sorted.map((entry) => ({
                                    ...entry,
                                    value: Number(entry.value).toLocaleString('en-US', {
                                        maximumFractionDigits: 3,
                                        useGrouping: false,
                                    }),
                                }));

                                return (
                                    <ChartTooltipContent active={active} payload={formatted} label={label} coordinate={coordinate} offset={offset} />
                                );
                            }}
                        />

                        <Line dataKey="points" type="linear" stroke="var(--color-primary)" strokeWidth={2} dot={false} />
                    </LineChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
};

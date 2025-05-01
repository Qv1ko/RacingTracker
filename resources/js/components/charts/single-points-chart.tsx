import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartConfig, ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';
import { CartesianGrid, Line, LineChart, XAxis, YAxis } from 'recharts';

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
                <ChartContainer config={chartConfig} className="h-[120px] w-full md:h-[240px] lg:h-[360px]">
                    <LineChart accessibilityLayer data={data}>
                        <CartesianGrid vertical={false} />
                        <XAxis dataKey="race" tickLine={false} axisLine={false} tickMargin={8} tickFormatter={(value) => value.slice(0, 3)} />
                        <YAxis orientation="left" tickLine={false} axisLine={false} tickMargin={8} />
                        <ChartTooltip cursor={false} content={<ChartTooltipContent hideLabel />} />
                        <Line dataKey="points" type="linear" stroke="var(--color-primary)" strokeWidth={2} dot={false} />
                    </LineChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
};

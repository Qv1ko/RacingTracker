'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartConfig, ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';
import { Bar, BarChart, CartesianGrid, LabelList, XAxis, YAxis } from 'recharts';

const chartConfig = {
    positions: {
        label: 'Number of times',
    },
} satisfies ChartConfig;

type PositionsChartProps = {
    title?: string;
    data: {
        position: string;
        times: number;
    }[];
};

export const PositionsChart: React.FC<PositionsChartProps> = ({ title = 'Positions history', data }) => {
    return (
        <Card className="mb-4">
            <CardHeader>
                <CardTitle className="text-center">{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <ChartContainer config={chartConfig} className="h-[480px] w-full">
                    <BarChart
                        accessibilityLayer
                        data={data}
                        margin={{
                            top: 12,
                        }}
                    >
                        <CartesianGrid vertical={false} />
                        <XAxis dataKey="position" tickLine={false} tickMargin={8} axisLine={false} />
                        <YAxis orientation="left" tickLine={false} axisLine={false} tickMargin={8} />
                        <ChartTooltip cursor={false} content={<ChartTooltipContent hideLabel />} />
                        <Bar dataKey="times" fill="var(--chart-1)">
                            <LabelList position="top" offset={12} className="fill-foreground" fontSize={12} />
                        </Bar>
                    </BarChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
};

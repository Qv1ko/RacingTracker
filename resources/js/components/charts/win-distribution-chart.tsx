import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    ChartConfig,
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
} from "@/components/ui/chart";
import { Cell, Pie, PieChart } from "recharts";

type WinDistributionChartProps = {
    data: {
        id: number;
        name: string;
        color: string;
        count: number;
    }[];
};

export function WinDistributionChart({ data }: WinDistributionChartProps) {
    const chartConfig = data.reduce<ChartConfig>((config, team) => {
        config[team.name] = { label: team.name, color: team.color };
        return config;
    }, {});

    return (
        <Card className="h-full">
            <CardHeader>
                <CardTitle className="text-center">Win distribution</CardTitle>
            </CardHeader>
            <CardContent>
                {data.length === 0 ? (
                    <div className="text-muted-foreground flex h-[360px] items-center justify-center text-center">
                        No team wins recorded for this season.
                    </div>
                ) : (
                    <ChartContainer
                        config={chartConfig}
                        className="h-[360px] w-full"
                        aria-label="Team win distribution"
                    >
                        <PieChart accessibilityLayer>
                            <ChartTooltip
                                content={
                                    <ChartTooltipContent
                                        hideLabel
                                        nameKey="name"
                                        formatter={(value, name) => (
                                            <>
                                                <span>{name}</span>
                                                <span className="text-foreground font-mono font-medium tabular-nums">
                                                    {value} {Number(value) === 1 ? "win" : "wins"}
                                                </span>
                                            </>
                                        )}
                                    />
                                }
                            />
                            <Pie
                                data={data}
                                dataKey="count"
                                nameKey="name"
                                innerRadius={72}
                                outerRadius={112}
                                paddingAngle={2}
                                strokeWidth={2}
                                stroke="var(--card)"
                            >
                                {data.map((team) => (
                                    <Cell key={team.id} fill={team.color} />
                                ))}
                            </Pie>
                            <ChartLegend
                                content={<ChartLegendContent nameKey="name" />}
                                verticalAlign="bottom"
                            />
                        </PieChart>
                    </ChartContainer>
                )}
            </CardContent>
        </Card>
    );
}

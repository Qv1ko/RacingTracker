import { useMemo, useState, type MouseEvent as ReactMouseEvent } from "react";

import {
    Brush,
    CartesianGrid,
    Line,
    LineChart,
    ReferenceLine,
    Tooltip,
    XAxis,
    YAxis,
} from "recharts";

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
    color?: string;
    data: {
        race: string;
        date: string;
        points: number;
    }[];
};

function MiniMap({
    points,
    startIndex,
    endIndex,
    onSelect,
    color,
}: {
    points: { points: number }[];
    startIndex: number;
    endIndex: number;
    onSelect: (centerIndex: number) => void;
    color: string;
}) {
    const W = 180;
    const H = 44;
    const len = points.length;
    const values = points.map((p) => p.points);
    const min = Math.min(...values);
    const max = Math.max(...values);
    const span = max - min || 1;

    const x = (i: number) => (len <= 1 ? 0 : (i / (len - 1)) * W);
    const y = (v: number) => H - 2 - ((v - min) / span) * (H - 4);

    const poly = points.map((p, i) => `${x(i)},${y(p.points)}`).join(" ");
    const rectX = x(startIndex);
    const rectW = Math.max(3, x(endIndex) - x(startIndex));

    const handleClick = (e: ReactMouseEvent<SVGSVGElement>) => {
        const rect = e.currentTarget.getBoundingClientRect();
        const frac = (e.clientX - rect.left) / rect.width;
        const center = Math.round(frac * (len - 1));
        onSelect(center);
    };

    return (
        <svg
            width={W}
            height={H}
            onClick={handleClick}
            className="cursor-pointer rounded border bg-muted/30"
            role="img"
            aria-label="Overview of all races; click to navigate"
        >
            <polyline points={poly} fill="none" stroke={color} strokeWidth={1.5} />
            <rect
                x={rectX}
                y={0}
                width={rectW}
                height={H}
                fill={color}
                fillOpacity={0.2}
                stroke={color}
                strokeWidth={1}
            />
        </svg>
    );
}

export const SinglePointsChart: React.FC<SinglePointsChartProps> = ({
    title = "Points history",
    color = "var(--color-primary)",
    data,
}) => {
    const points = useMemo(() => downsample(data), [data]);

    const lineColor = color ?? "var(--color-primary)";

    const MAX_WINDOW = 25;
    const [range, setRange] = useState<{ startIndex: number; endIndex: number }>({
        startIndex: Math.max(0, points.length - MAX_WINDOW),
        endIndex: points.length - 1,
    });
    const showControls = points.length > MAX_WINDOW;

    const [hover, setHover] = useState<number | null>(null);

    const clampRange = (next: { startIndex: number; endIndex: number }) => {
        const len = points.length;
        const s = Math.max(0, Math.min(next.startIndex, len - 1));
        const e = Math.max(s, Math.min(next.endIndex, len - 1));
        if (e - s > MAX_WINDOW - 1) {
            setRange({
                startIndex: s,
                endIndex: Math.min(len - 1, s + MAX_WINDOW - 1),
            });
        } else {
            setRange({ startIndex: s, endIndex: e });
        }
    };

    return (
        <Card className="mb-4">
            <CardHeader>
                <CardTitle className="text-center">{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <div className="relative">
                    <ChartContainer config={chartConfig} className="h-[360px] w-full">
                        <LineChart
                            accessibilityLayer
                            data={points}
                            margin={{ top: 12, left: 8, right: 12 }}
                            onMouseMove={(state) => {
                                const i = state?.activeTooltipIndex;
                                setHover(typeof i === "number" ? i : null);
                            }}
                            onMouseLeave={() => setHover(null)}
                        >
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
                                cursor={false}
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
                            {showControls && (
                                <Brush
                                    dataKey="race"
                                    height={22}
                                    stroke="var(--border)"
                                    fill="var(--card)"
                                    travellerWidth={0}
                                    traveller={() => <g pointerEvents="none" />}
                                    startIndex={range.startIndex}
                                    endIndex={range.endIndex}
                                    onChange={(r) => {
                                        const len = points.length;
                                        const snapped =
                                            Math.round((r.startIndex ?? 0) / MAX_WINDOW) *
                                            MAX_WINDOW;
                                        const start = Math.max(
                                            0,
                                            Math.min(snapped, len - MAX_WINDOW),
                                        );
                                        setRange({
                                            startIndex: start,
                                            endIndex: start + MAX_WINDOW - 1,
                                        });
                                    }}
                                    tickFormatter={() => ""}
                                />
                            )}

                            {hover != null && points[hover] && (
                                <ReferenceLine
                                    x={points[hover].race}
                                    stroke="var(--muted-foreground)"
                                    strokeDasharray="3 3"
                                    strokeWidth={1}
                                />
                            )}
                            <Line
                                dataKey="points"
                                type="linear"
                                stroke={lineColor}
                                strokeWidth={2}
                                dot={{ r: 2, strokeWidth: 1 }}
                                isAnimationActive={false}
                            />
                        </LineChart>
                    </ChartContainer>
                    {showControls && (
                        <div className="absolute left-20 top-0 z-10 rounded border bg-background/80 p-1">
                            <MiniMap
                                points={points}
                                startIndex={range.startIndex}
                                endIndex={range.endIndex}
                                color={lineColor}
                                onSelect={(center) => {
                                    const w = range.endIndex - range.startIndex;
                                    const ns = Math.max(
                                        0,
                                        Math.min(center - Math.floor(w / 2), points.length - 1 - w),
                                    );
                                    clampRange({ startIndex: ns, endIndex: ns + w });
                                }}
                            />
                        </div>
                    )}
                </div>
            </CardContent>
        </Card>
    );
};

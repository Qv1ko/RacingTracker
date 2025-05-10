import { Card, CardContent } from '@/components/ui/card';

interface StatCardProps {
    mainValue: string | number;
    subValue?: string | number;
    label: string;
}

export default function StatCard({ mainValue, subValue, label }: StatCardProps) {
    return (
        <Card className="border-0">
            <CardContent className="flex flex-col items-center justify-center p-6 text-center">
                <div className="flex items-baseline">
                    <span className="text-primary text-4xl font-bold">{mainValue}</span>
                    {subValue && <span className="ml-1 text-sm text-gray-500">/{subValue}</span>}
                </div>
                <p className="mt-2 text-xs font-medium tracking-wider uppercase">{label}</p>
            </CardContent>
        </Card>
    );
}

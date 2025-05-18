import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import React from 'react';

type Activity = {
    position: string;
    name: string;
    date: string; // ISO format: "YYYY-MM-DDTHH:mm:ss"
};

type ActivityChartProps = {
    data: {
        activity: Activity[];
    };
};

export const ActivityChart: React.FC<ActivityChartProps> = ({ data }) => {
    const getColorFromStatus = (position: string): string => {
        const gold = 'gold';
        const silver = 'silver';
        const bronze = '#CD7F32';
        const blue = '#007BFF';
        const red = 'red';

        switch (position) {
            case '1':
                return gold;
            case '2':
                return silver;
            case '3':
                return bronze;
            default:
                return parseInt(position) ? blue : red;
        }
    };

    const formatDate = (isoDate: string): string => {
        return isoDate.split('T')[0].split('-').reverse().join('/');
    };

    return (
        <div className="rounded-sm border">
            <h3 className="mt-4 text-center font-medium">Activity history</h3>
            <div className="m-8 mt-4 flex flex-wrap gap-1">
                {data.activity.map((activity, index) => (
                    <TooltipProvider key={index}>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="16" height="16" fill={getColorFromStatus(activity.position)} />
                                </svg>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>
                                    {activity.position} - {activity.name} ({formatDate(activity.date)})
                                </p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                ))}
            </div>
        </div>
    );
};

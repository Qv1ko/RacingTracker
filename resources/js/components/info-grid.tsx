import type React from 'react';

interface InfoGridProps {
    data: {
        key: string;
        value: React.ReactNode;
    }[];
}

export default function InfoGrid({ data }: InfoGridProps) {
    data = data.filter((item) => item.value);

    const isOdd = data.length % 2 !== 0;
    const middleIndex = Math.ceil(data.length / 2);

    const leftColumnItems = data.slice(0, isOdd ? middleIndex - 1 : middleIndex);
    const rightColumnItems = data.slice(isOdd ? middleIndex - 1 : middleIndex, isOdd ? data.length - 1 : data.length);
    const lastItem = isOdd ? data[data.length - 1] : null;

    return (
        <div className="overflow-hidden rounded-md border">
            <div className="grid grid-cols-1 md:grid-cols-2">
                <div className="flex flex-col divide-y md:border-r">
                    {leftColumnItems.map((info, index) => (
                        <div key={`left-${index}`} className="flex items-center justify-between px-4 py-3">
                            <div className="font-medium">{info.key}</div>
                            <div className="text-right">{info.value}</div>
                        </div>
                    ))}
                </div>
                <div className="flex flex-col divide-y border-t md:border-t-0">
                    {rightColumnItems.map((info, index) => (
                        <div key={`right-${index}`} className="flex items-center justify-between px-4 py-3">
                            <div className="font-medium">{info.key}</div>
                            <div className="text-right">{info.value}</div>
                        </div>
                    ))}
                </div>
            </div>
            {lastItem && (
                <div className="flex items-center justify-between border-t px-4 py-3">
                    <div className="font-medium">{lastItem.key}</div>
                    <div className="text-right">{lastItem.value}</div>
                </div>
            )}
        </div>
    );
}

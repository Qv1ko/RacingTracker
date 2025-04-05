import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/react';

export const SelectSeason = ({ seasons, url }: { seasons: string[]; url: string }) => {
    const handleSelectChange = (season: string) => {
        router.get(url + `?season=${season}`);
    };

    return (
        <div className="mb-4">
            <Select onValueChange={handleSelectChange}>
                <SelectTrigger className="w-[180px]">
                    <SelectValue placeholder="Select season" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Season</SelectLabel>
                        <SelectItem value="all">All seasons</SelectItem>
                        {Array.from(seasons).map((season) => (
                            <SelectItem value={season}>{season}</SelectItem>
                        ))}
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
    );
};

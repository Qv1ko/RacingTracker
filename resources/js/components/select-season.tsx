import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { router } from "@inertiajs/react";

export const SelectSeason = ({
    all = true,
    seasons,
    selectedValue,
    url,
    compact = false,
}: {
    all?: boolean;
    seasons: string[];
    selectedValue: string;
    url: string;
    compact?: boolean;
}) => {
    const handleSelectChange = (season: string) => {
        router.get(url, { season }, { preserveScroll: true });
    };

    return (
        <div className={compact ? "mb-0" : "mb-4"}>
            <Select
                value={selectedValue !== "" ? selectedValue : seasons[0]}
                onValueChange={handleSelectChange}
            >
                <SelectTrigger className={compact ? "h-9 w-[110px]" : "w-[180px]"}>
                    <SelectValue placeholder="Select season" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        {all && (
                            <SelectItem key="select-all" value="all">
                                All seasons
                            </SelectItem>
                        )}
                        {seasons.map((season) => (
                            <SelectItem key={"select-" + season} value={season}>
                                {season}
                            </SelectItem>
                        ))}
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
    );
};

import { useEffect, useRef, useState } from "react";

import FlagIcon from "@/components/ui/flag-icon";
import { Input } from "@/components/ui/input";
import { Icon } from "@/components/icon";
import { Search } from "lucide-react";
import { Link } from "@inertiajs/react";

interface SearchResult {
    id?: number;
    name?: string;
    nationality?: string | null;
    date?: string;
    year?: string;
    href: string;
}

interface SearchResponse {
    drivers: SearchResult[];
    teams: SearchResult[];
    races: SearchResult[];
    seasons: SearchResult[];
}

const groupLabels: Record<keyof SearchResponse, string> = {
    drivers: "Drivers",
    teams: "Teams",
    races: "Races",
    seasons: "Seasons",
};

export function GlobalSearch() {
    const [query, setQuery] = useState("");
    const [results, setResults] = useState<SearchResponse>({
        drivers: [],
        teams: [],
        races: [],
        seasons: [],
    });
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const term = query.trim();

        if (term.length < 2) {
            setResults({ drivers: [], teams: [], races: [], seasons: [] });
            setLoading(false);
            return;
        }

        setLoading(true);
        const handle = window.setTimeout(() => {
            fetch(`/search?q=${encodeURIComponent(term)}`)
                .then((res) => res.json())
                .then((data: SearchResponse) => setResults(data))
                .catch(() => setResults({ drivers: [], teams: [], races: [], seasons: [] }))
                .finally(() => setLoading(false));
        }, 250);

        return () => window.clearTimeout(handle);
    }, [query]);

    useEffect(() => {
        function onClickOutside(event: MouseEvent) {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setOpen(false);
            }
        }

        document.addEventListener("mousedown", onClickOutside);
        return () => document.removeEventListener("mousedown", onClickOutside);
    }, []);

    const hasResults =
        results.drivers.length > 0 ||
        results.teams.length > 0 ||
        results.races.length > 0 ||
        results.seasons.length > 0;

    const flatResults = [
        ...results.drivers,
        ...results.teams,
        ...results.races,
        ...results.seasons,
    ];

    function onKeyDown(event: React.KeyboardEvent<HTMLInputElement>) {
        if (event.key === "Escape") {
            setOpen(false);
        } else if (event.key === "Enter" && flatResults.length > 0) {
            window.location.href = flatResults[0].href;
        }
    }

    return (
        <div ref={containerRef} className="relative w-32 sm:w-48 md:w-64">
            <div className="relative">
                <Icon
                    iconNode={Search}
                    className="text-muted-foreground pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2"
                />
                <Input
                    type="search"
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    onFocus={() => setOpen(true)}
                    onKeyDown={onKeyDown}
                    placeholder="Search…"
                    className="pl-8"
                    aria-label="Search drivers, teams, races and seasons"
                />
            </div>

            {open && query.trim().length >= 2 && (
                <div className="bg-popover text-popover-foreground absolute z-50 mt-1 max-h-96 w-full overflow-y-auto rounded-md border shadow-lg">
                    {loading && <div className="text-muted-foreground p-3 text-sm">Searching…</div>}

                    {!loading && !hasResults && (
                        <div className="text-muted-foreground p-3 text-sm">No results found.</div>
                    )}

                    {!loading &&
                        hasResults &&
                        (Object.keys(groupLabels) as Array<keyof SearchResponse>).map((group) =>
                            results[group].length > 0 ? (
                                <div key={group} className="py-1">
                                    <div className="text-muted-foreground px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                                        {groupLabels[group]}
                                    </div>
                                    {results[group].map((result, index) => (
                                        <Link
                                            key={`${group}-${result.id ?? result.year}-${index}`}
                                            href={result.href}
                                            onClick={() => setOpen(false)}
                                            className="flex items-center gap-2 px-3 py-1.5 text-sm hover:bg-accent"
                                        >
                                            {result.nationality && (
                                                <FlagIcon
                                                    nationality={result.nationality}
                                                    size={18}
                                                />
                                            )}
                                            <span className="truncate">
                                                {result.name ?? result.year}
                                            </span>
                                            {result.date && (
                                                <span className="text-muted-foreground ml-auto text-xs">
                                                    {result.date}
                                                </span>
                                            )}
                                        </Link>
                                    ))}
                                </div>
                            ) : null,
                        )}
                </div>
            )}
        </div>
    );
}

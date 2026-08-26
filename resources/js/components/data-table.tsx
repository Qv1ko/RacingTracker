import { Button } from "@/components/ui/button";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { useState } from "react";
import {
    ColumnDef,
    createPaginatedRowModel,
    createSortedRowModel,
    flexRender,
    PaginationState,
    RowData,
    rowPaginationFeature,
    columnVisibilityFeature,
    rowSortingFeature,
    SortingState,
    tableFeatures,
    useTable,
} from "@tanstack/react-table";

const features = tableFeatures({
    rowPaginationFeature,
    columnVisibilityFeature,
    paginatedRowModel: createPaginatedRowModel(),
    rowSortingFeature,
    sortedRowModel: createSortedRowModel(),
});

export type DataTableColumn<TData extends RowData> = ColumnDef<typeof features, TData, any>;

export function getColumnKey<TData extends RowData>(
    column: DataTableColumn<TData>,
): string | undefined {
    if ("accessorKey" in column && typeof column.accessorKey === "string") {
        return column.accessorKey;
    }

    if ("id" in column) {
        return column.id;
    }

    return undefined;
}

interface DataTableProps<TData extends RowData> {
    columns: ColumnDef<typeof features, TData, any>[];
    data: TData[];
    initialSorting?: SortingState;
}

export function DataTable<TData extends RowData>({
    columns,
    data,
    initialSorting,
}: DataTableProps<TData>) {
    const [pagination, setPagination] = useState<PaginationState>({
        pageIndex: 0,
        pageSize: 25,
    });

    const table = useTable({
        features,
        data,
        columns,
        state: { pagination },
        onPaginationChange: setPagination,
        initialState: {
            sorting: initialSorting ?? [],
        },
    });

    return (
        <div>
            <div className="rounded-sm border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    const canSort = header.column.getCanSort();
                                    const sorted = header.column.getIsSorted();

                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder ? null : canSort ? (
                                                <button
                                                    type="button"
                                                    className="flex items-center gap-1 font-bold hover:text-primary"
                                                    onClick={header.column.getToggleSortingHandler()}
                                                >
                                                    {flexRender(
                                                        header.column.columnDef.header,
                                                        header.getContext(),
                                                    )}
                                                    <span className="text-xs">
                                                        {sorted === "asc"
                                                            ? "▲"
                                                            : sorted === "desc"
                                                              ? "▼"
                                                              : "↕"}
                                                    </span>
                                                </button>
                                            ) : (
                                                flexRender(
                                                    header.column.columnDef.header,
                                                    header.getContext(),
                                                )
                                            )}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow key={row.id}>
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            {flexRender(
                                                cell.column.columnDef.cell,
                                                cell.getContext(),
                                            )}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="h-24 text-center">
                                    No results.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>
            <div className="flex items-center justify-between space-x-2 py-4">
                <span className="text-muted-foreground text-sm">
                    Page {pagination.pageIndex + 1} of{" "}
                    {table.getPageCount() || 1}
                </span>
                <div className="flex items-center space-x-4">
                    <div className="flex items-center space-x-2">
                        <span className="text-muted-foreground text-sm">
                            Rows per page
                        </span>
                        <Select
                            value={String(pagination.pageSize)}
                            onValueChange={(value) =>
                                setPagination((prev) => ({
                                    ...prev,
                                    pageSize: Number(value),
                                    pageIndex: 0,
                                }))
                            }
                        >
                            <SelectTrigger className="h-8 w-[72px]">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {[25, 50, 100].map((size) => (
                                    <SelectItem key={size} value={String(size)}>
                                        {size}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => table.previousPage()}
                        disabled={!table.getCanPreviousPage()}
                    >
                        Previous
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => table.nextPage()}
                        disabled={!table.getCanNextPage()}
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    );
}

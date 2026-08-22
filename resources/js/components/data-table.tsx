import { Button } from "@/components/ui/button";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import {
    ColumnDef,
    createPaginatedRowModel,
    flexRender,
    RowData,
    rowPaginationFeature,
    columnVisibilityFeature,
    tableFeatures,
    useTable,
} from "@tanstack/react-table";

const features = tableFeatures({
    rowPaginationFeature,
    columnVisibilityFeature,
    paginatedRowModel: createPaginatedRowModel(),
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
}

export function DataTable<TData extends RowData>({ columns, data }: DataTableProps<TData>) {
    const table = useTable({
        features,
        data,
        columns,
        initialState: {
            pagination: {
                pageIndex: 0,
                pageSize: 32,
            },
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
                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(
                                                      header.column.columnDef.header,
                                                      header.getContext(),
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
            <div className="flex items-center justify-end space-x-2 py-4">
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
    );
}

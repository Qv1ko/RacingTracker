import { Inbox } from "lucide-react";
import { useId, type ReactNode } from "react";

import { Card, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { cn } from "@/lib/utils";

interface EmptyStateProps {
    title: string;
    description?: ReactNode;
    action?: ReactNode;
    icon?: ReactNode;
    className?: string;
}

export function EmptyState({
    title,
    description,
    action,
    icon = <Inbox aria-hidden="true" />,
    className,
}: EmptyStateProps) {
    const titleId = useId();
    const descriptionId = `${titleId}-description`;

    return (
        <Card
            role="status"
            aria-labelledby={titleId}
            aria-describedby={description ? descriptionId : undefined}
            className={cn("items-center text-center shadow-none", className)}
        >
            <CardHeader className="items-center gap-2">
                <div
                    aria-hidden="true"
                    className="text-muted-foreground flex size-9 items-center justify-center rounded-sm border"
                >
                    {icon}
                </div>
                <CardTitle>
                    <h3 id={titleId}>{title}</h3>
                </CardTitle>
                {description && <CardDescription id={descriptionId}>{description}</CardDescription>}
            </CardHeader>
            {action && <CardFooter>{action}</CardFooter>}
        </Card>
    );
}

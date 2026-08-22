const MAX_POINTS = 800;

/**
 * Stride-samples a dataset down to at most MAX_POINTS entries, always
 * keeping the first and last items so the line shape stays anchored.
 */
export function downsample<T>(data: T[], maxPoints: number = MAX_POINTS): T[] {
    if (data.length <= maxPoints) {
        return data;
    }

    const step = (data.length - 1) / (maxPoints - 1);
    const sampled: T[] = [];

    for (let i = 0; i < maxPoints - 1; i++) {
        sampled.push(data[Math.round(i * step)]);
    }

    sampled.push(data[data.length - 1]);

    return sampled;
}

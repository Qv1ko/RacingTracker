import { z } from "zod";

export const driverSchema = z.object({
  name: z.string().trim().max(50, "The name is obligatory"),
  surname: z.string().max(100, "The surname is obligatory"),
  nationality: z.string().max(100).optional(),
});

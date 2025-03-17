import type { NextApiRequest, NextApiResponse } from "next";
import { ZodSchema } from "zod";

export async function validateRequest(
  req: NextApiRequest,
  res: NextApiResponse,
  schema: ZodSchema
) {
  try {
    const parsedBody = schema.safeParse(req.body);
    if (!parsedBody.success) {
      return res.status(400).json({ error: parsedBody.error.errors });
    }
    req.body = parsedBody.data; // Sobrescribe el body con los datos validados
  } catch (error) {
    return res
      .status(500)
      .json({ error: `Error en la validación del request: ${error}` });
  }
}

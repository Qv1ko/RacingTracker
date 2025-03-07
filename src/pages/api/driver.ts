import type { NextApiRequest, NextApiResponse } from "next";
import {
  createDriver,
  updateDriver,
  deleteDriver,
} from "@/controllers/driverController";

export default async function handler(
  req: NextApiRequest,
  res: NextApiResponse
) {
  try {
    res.setHeader("Content-Type", "application/json; charset=utf-8");
    switch (req.method) {
      case "POST": {
        // Datos del body para insertar un nuevo piloto
        const { name, surname, nationality } = req.body;
        if (!name || !surname) {
          return res
            .status(400)
            .json({ error: "Required fields are missing." });
        }
        const newDriver = await createDriver({ name, surname, nationality });
        return res.status(201).json(newDriver);
      }
      case "PATCH": {
        const { id } = req.query;
        if (!id) {
          return res
            .status(400)
            .json({ error: "The ID is mandatory to update the driver." });
        }
        const { name, surname, nationality } = req.body;
        if (!name || !surname) {
          return res
            .status(400)
            .json({ error: "Required fields are missing." });
        }
        const updatedDriver = await updateDriver({
          id: Number(id),
          name,
          surname,
          nationality,
        });
        return res.status(200).json(updatedDriver);
      }
      case "DELETE": {
        const { id } = req.query;
        if (!id) {
          return res
            .status(400)
            .json({ error: "The ID is mandatory for deletion." });
        }
        const deletedDriver = await deleteDriver(Number(id));
        return res.status(200).json(deletedDriver);
      }
      default: {
        res.setHeader("Allow", ["POST", "DELETE"]);
        return res.status(405).end(`Method ${req.method} not allowed`);
      }
    }
  } catch (error: unknown) {
    return res.status(500).json({
      error:
        error instanceof Error
          ? `ERROR 500: ${error.message}`
          : "ERROR 500: Internal server error",
    });
  }
}

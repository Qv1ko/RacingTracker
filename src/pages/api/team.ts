import type { NextApiRequest, NextApiResponse } from "next";
import {
  createTeam,
  updateTeam,
  deleteTeam,
} from "@/controllers/teamController";

export default async function handler(
  req: NextApiRequest,
  res: NextApiResponse
) {
  try {
    res.setHeader("Content-Type", "application/json; charset=utf-8");
    switch (req.method) {
      case "POST": {
        // Datos del body para insertar un nuevo piloto
        const { name, nationality } = req.body;
        if (!name) {
          return res
            .status(400)
            .json({ error: "Required fields are missing." });
        }
        const newTeam = await createTeam({ name, nationality });
        return res.status(201).json(newTeam);
      }
      case "PATCH": {
        const { id } = req.query;
        if (!id) {
          return res
            .status(400)
            .json({ error: "The ID is mandatory to update the team." });
        }
        const { name, nationality } = req.body;
        if (!name) {
          return res
            .status(400)
            .json({ error: "Required fields are missing." });
        }
        const updatedTeam = await updateTeam({
          id: Number(id),
          name,
          nationality,
        });
        return res.status(200).json(updatedTeam);
      }
      case "DELETE": {
        const { id } = req.query;
        if (!id) {
          return res
            .status(400)
            .json({ error: "The ID is mandatory for deletion." });
        }
        const deletedTeam = await deleteTeam(Number(id));
        return res.status(200).json(deletedTeam);
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

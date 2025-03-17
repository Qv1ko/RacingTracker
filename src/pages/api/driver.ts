import type { NextApiRequest, NextApiResponse } from "next";
import { validateRequest } from "@/middlewares/validateRequest";
import { driverSchema } from "@/schemas/driverSchema";
import {
  createDriver,
  updateDriver,
  deleteDriver,
  getDriver,
  getAllDrivers,
} from "@/controllers/driverController";

export default async function handler(
  req: NextApiRequest,
  res: NextApiResponse
) {
  // The JSON response header
  res.setHeader("Content-Type", "application/json; charset=utf-8");

  try {
    const method = req.method;
    if (!method) {
      return res.status(405).json({ error: "Method not allowed" });
    }

    switch (method) {
      case "GET": {
        const { id } = req.query;
        if (id) {
          const driverId = Number(id);
          if (isNaN(driverId)) {
            return res.status(400).json({ error: "Invalid ID" });
          }
          const driver = await getDriver(driverId);
          if (!driver) {
            return res.status(404).json({ error: "Driver not found" });
          }
          return res.status(200).json({ ...driver, id: Number(driver.id) });
        } else {
          const drivers = await getAllDrivers();
          // Numeric IDs
          const parsedDrivers = drivers.map((driver) => ({
            ...driver,
            id: Number(driver.id),
          }));
          return res.status(200).json(parsedDrivers);
        }
      }
      case "POST": {
        // Driver data validation
        await validateRequest(req, res, driverSchema);
        const { name, surname, nationality } = req.body;
        const newDriver = await createDriver({ name, surname, nationality });
        return res.status(201).json({ ...newDriver, id: Number(newDriver.id) });
      }
      case "PATCH": {
        const { id } = req.query;
        if (!id) {
          return res
            .status(400)
            .json({ error: "The ID is mandatory for update." });
        }
        const driverId = Number(id);
        if (isNaN(driverId)) {
          return res.status(400).json({ error: "Invalid ID" });
        }
        await validateRequest(req, res, driverSchema.partial());
        const { name, surname, nationality } = req.body;
        const updatedDriver = await updateDriver({
          id: driverId,
          name,
          surname,
          nationality,
        });
        if (!updatedDriver) {
          return res.status(404).json({ error: "Driver not found" });
        }
        return res
          .status(200)
          .json({ ...updatedDriver, id: Number(updatedDriver.id) });
      }
      case "DELETE": {
        const { id } = req.query;
        if (!id) {
          return res
            .status(400)
            .json({ error: "The ID is mandatory for deletion." });
        }
        const driverId = Number(id);
        if (isNaN(driverId)) {
          return res.status(400).json({ error: "Invalid ID" });
        }
        console.log(`Deleting driver with ID: ${driverId}`);
        const deletedDriver = await deleteDriver(driverId);
        if (!deletedDriver) {
          return res.status(404).json({ error: "Driver not found" });
        }
        return res
          .status(200)
          .json({ ...deletedDriver, id: Number(deletedDriver.id) });
      }
      default: {
        res.setHeader("Allow", ["GET", "POST", "PATCH", "DELETE"]);
        return res
          .status(405)
          .end(`Method ${req.method} is not allowed on this endpoint`);
      }
    }
  } catch (error: unknown) {
    console.error("API Error: ", error);
    return res.status(500).json({
      error:
        error instanceof Error
          ? `ERROR 500: ${error.message}`
          : "ERROR 500: Internal server error",
    });
  }
}

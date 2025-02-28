import prisma from "../models/prismaClient";
import type { Driver } from "@prisma/client";

export const getAllDrivers = async (): Promise<Driver[]> => {
  return await prisma.driver.findMany({
    orderBy: { surname: "asc" },
  });
};

export const createDriver = async (data: {
  name: string;
  surname: string;
  nationality?: string;
}): Promise<Driver> => {
  return await prisma.driver.create({
    data: {
      name: data.name,
      surname: data.surname,
      nationality: data.nationality,
      participations: {},
    },
  });
};

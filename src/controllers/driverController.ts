import prisma from "../models/prismaClient";
import type { Driver } from "@prisma/client";

export type { Driver };

export const getAllDrivers = async (): Promise<Driver[]> => {
  return await prisma.driver.findMany({
    orderBy: { name: "desc" },
  });
};

interface CreateDriverInput {
  name: string;
  surname: string;
  nationality?: string;
}

export const createDriver = async (
  data: CreateDriverInput
): Promise<Driver> => {
  return await prisma.driver.create({
    data: {
      name: data.name,
      surname: data.surname,
      nationality: data.nationality,
      participations: {},
    },
  });
};

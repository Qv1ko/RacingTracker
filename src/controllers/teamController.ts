import prisma from "../models/prismaClient";
import type { Team } from "@prisma/client";

export const getAllTeams = async (): Promise<Team[]> => {
  return await prisma.team.findMany({
    orderBy: { name: "asc" },
  });
};

export const createTeam = async (data: {
  name: string;
  nationality?: string;
}): Promise<Team> => {
  return await prisma.team.create({
    data: {
      name: data.name,
      nationality: data.nationality,
      participations: {},
    },
  });
};

export const updateTeam = async (data: {
  id: number;
  name: string;
  nationality?: string | null;
}): Promise<Team> => {
  return await prisma.team.update({
    where: { id: data.id },
    data: {
      name: data.name,
      nationality: data.nationality ?? null,
    },
  });
};

export const deleteTeam = async (id: number): Promise<Team> => {
  return await prisma.team.delete({
    where: { id },
  });
};

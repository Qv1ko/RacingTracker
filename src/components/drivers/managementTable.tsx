"use client";

import type React from "react";
import Image from "next/image";
import FlagIcon from "@/components/flagIcon";

interface Driver {
  id: number;
  name: string;
  surname: string;
  nationality: string | null;
}

interface DriversManagementTableProps {
  drivers: Driver[];
  onEdit: (driver: Driver) => void;
  onDelete: (driver: Driver) => void;
}

export const DriversManagementTable: React.FC<DriversManagementTableProps> = ({
  drivers: drivers,
  onEdit,
  onDelete,
}) => {
  return (
    <div className="overflow-hidden border border-gray-200 rounded-sm dark:border-gray-700">
      <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead className="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th
              scope="col"
              className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400"
            >
              Name
            </th>
            <th
              scope="col"
              className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400"
            >
              Surname
            </th>
            <th
              scope="col"
              className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400"
            >
              Nationality
            </th>
            <th
              scope="col"
              className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
          {drivers.map((driver) => (
            <>
              <tr className="transition-all duration-200 group bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 relative group-hover:before:content-[''] group-hover:before:absolute group-hover:before:left-0 group-hover:before:top-0 group-hover:before:h-full group-hover:before:w-1 group-hover:before:bg-orange-500">
                  {driver.name}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {driver.surname}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  {driver.nationality ? (
                    <div className="flex items-center">
                      <div className="flex-shrink-0 w-6 h-4 mr-2">
												<FlagIcon nationality={driver.nationality} size={24} />
                      </div>
                      <span className="text-sm text-gray-500 dark:text-gray-400">
                        {driver.nationality}
                      </span>
                    </div>
                  ) : null}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex space-x-2">
                    <button
                      className="p-2 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                      onClick={() => onEdit(driver)}
                    >
                      <Image
                        className="dark:invert"
                        src="/edit.svg"
                        alt="Edit"
                        width={16}
                        height={16}
                        priority
                      />
                      <span className="sr-only">Edit</span>
                    </button>
                    <button
                      className="p-2 rounded-md border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                      onClick={() => onDelete(driver)}
                    >
                      <Image
                        className="dark:invert"
                        src="/trash.svg"
                        alt="Delete"
                        width={16}
                        height={16}
                        priority
                      />
                      <span className="sr-only">Delete</span>
                    </button>
                  </div>
                </td>
              </tr>
            </>
          ))}
        </tbody>
      </table>
    </div>
  );
};

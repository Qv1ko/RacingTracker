"use client";

import { nationalities } from "@/controllers/utilsController";
import type React from "react";

interface DriverFormProps {
  formData: {
		name: string;
		surname: string;
		nationality: string;
	};
  onChange: (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
  ) => void;
	title: string;
  idPrefix: string;
}

export const DriverForm: React.FC<DriverFormProps> = ({
  formData,
  onChange,
	title,
  idPrefix,
}) => {
  return (
    <div className="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6">
      <div className="mb-4">
        <h3 className="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
          {title}
        </h3>
        <div className="grid grid-cols-1 gap-4">
          <div>
            <label
              htmlFor={`${idPrefix}name`}
              className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
            >
              Name
            </label>
            <input
              type="text"
              name="name"
              id={`${idPrefix}name`}
              required
              className="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
              value={formData.name}
              onChange={onChange}
            />
          </div>
          <div>
            <label
              htmlFor={`${idPrefix}surname`}
              className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
            >
              Surname
            </label>
            <input
              type="text"
              name="surname"
              id={`${idPrefix}surname`}
              required
              className="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
              value={formData.surname}
              onChange={onChange}
            />
          </div>
          <div>
            <label
              htmlFor={`${idPrefix}nationality`}
              className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
            >
              Nacionality
            </label>
            <select
              name="nationality"
              id={`${idPrefix}nationality`}
              className="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
              value={formData.nationality}
              onChange={onChange}
            >
              <option value="">Select nacionality</option>
              {nationalities.map((nationality) => (
                <option
                  key={nationality.toLowerCase().replace(" ", "_")}
                  value={nationality}
                >
                  {nationality}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>
    </div>
  );
};

"use client";

import type React from "react";
import Modal from "@/components/modals/modal";
import { DriverForm } from "@/components/drivers/driverForm";

interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  formData: any & { id: number };
  onChange: (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
  ) => void;
  onSubmit: (e: React.FormEvent) => void;
}

export const EditModal: React.FC<EditModalProps> = ({
  isOpen,
  onClose,
  formData,
  onChange,
  onSubmit,
}) => {
  return (
    <Modal isOpen={isOpen} onClose={onClose}>
      <DriverForm
        formData={formData}
        onChange={onChange}
        title="Edit driver"
        idPrefix="edit-"
      />
      <div className="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
        <button
          type="button"
          className="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
          onClick={onSubmit}
        >
          Save
        </button>
        <button
          type="button"
          className="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
          onClick={onClose}
        >
          Cancel
        </button>
      </div>
    </Modal>
  );
};

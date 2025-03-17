import { useEffect, useState } from "react";

export interface Driver {
  id: number;
  name: string;
  surname: string;
  nationality: string | null;
}

export const useDrivers = (drivers: Driver[]) => {
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [selectedDriver, setSelectedDriver] = useState<Driver | null>(null);
  const [searchTerm, setSearchTerm] = useState("");
  const [filteredDrivers, setFilteredDrivers] = useState<Driver[]>(drivers);
  const [editFormData, setEditFormData] = useState<Driver>({
    id: 0,
    name: "",
    surname: "",
    nationality: "",
  });
  const [newDriver, setNewDriver] = useState<Driver>({
    name: "",
    surname: "",
    nationality: "",
  } as Driver);

  useEffect(() => {
    const results = drivers.filter(
      (driver) =>
        driver.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        driver.surname.toLowerCase().includes(searchTerm.toLowerCase())
    );
    setFilteredDrivers(results);
  }, [searchTerm, drivers]);

  const handleEdit = (driver: Driver) => {
    setEditFormData({
      id: driver.id,
      name: driver.name,
      surname: driver.surname,
      nationality: driver.nationality || "",
    });
    setEditModalOpen(true);
  };

  const handleDeleteClick = (driver: Driver) => {
    setSelectedDriver(driver);
    setDeleteModalOpen(true);
  };

  const confirmDelete = async () => {
    if (selectedDriver) {
      try {
        const response = await fetch(`/api/driver?id=${selectedDriver.id}`, {
          method: "DELETE",
        });

        if (!response.ok) {
          throw new Error("Error deleting driver" + response.status);
        }

        console.log(`Deleting driver with ID: ${selectedDriver.id}`);
      } catch (error) {
        console.error("Error deleting driver:", error);
      }
      setDeleteModalOpen(false);
      setSelectedDriver(null);
    }
  };

  const handleEditInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
  ) => {
    const { name, value } = e.target;
    setEditFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleCreateInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
  ) => {
    const { name, value } = e.target;
    setNewDriver((prev) => ({ ...prev, [name]: value }));
  };

  const handleEditSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const response = await fetch(`/api/driver?id=${editFormData.id}`, {
        method: "PATCH",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(editFormData),
      });

      if (!response.ok) {
        throw new Error("Error updating driver");
      }

      console.log("Driver updated successfully");
    } catch (error) {
      console.error("Error updating driver:", error);
    }
    setEditModalOpen(false);
    setEditFormData({
      id: 0,
      name: "",
      surname: "",
      nationality: "",
    });
  };

  const handleCreateSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const response = await fetch("/api/driver", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(newDriver),
      });

      if (!response.ok) {
        const errorData = await response.json();
        console.log("Error creating driver", response.status, errorData);
      }

      console.log(
        `Driver ${newDriver.name} ${newDriver.surname} created successfully`
      );
    } catch (error) {
      console.error("Error creating driver:", error);
    }
    setCreateModalOpen(false);
    setNewDriver({ name: "", surname: "", nationality: "" } as Driver);
  };

  return {
    // States
    deleteModalOpen,
    createModalOpen,
    editModalOpen,
    selectedDriver,
    searchTerm,
    filteredDrivers,
    editFormData,
    newDriver,

    // Actions
    setDeleteModalOpen,
    setCreateModalOpen,
    setEditModalOpen,
    setSearchTerm,
    handleEdit,
    handleEditInputChange,
    handleEditSubmit,
    handleDeleteClick,
    confirmDelete,
    handleCreateInputChange,
    handleCreateSubmit,
  };
};

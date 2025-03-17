import { useDrivers } from "@/hooks/useDriversManagement";
import { CreateModal } from "@/components/modals/createModal";
import { DeleteModal } from "@/components/modals/deleteModal";
import { EditModal } from "@/components/modals/editModal";
import FieldSearchBar from "@/components/fieldSearchBar";
import { DriversManagementTable } from "@/components/drivers/managementTable";
import Image from "next/image";
import { useFetch } from "@/hooks/useFetch";
import Loading from "@/components/loading";
import ErrorMessage from "@/components/errorMessage";

interface Driver {
  id: number;
  name: string;
  surname: string;
  nationality: string | null;
}

const DriversManagementPage = () => {
    const { data, loading, error } = useFetch<Driver[]>("/api/driver");
  const driversData = data || [];

  const {
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
  } = useDrivers(driversData);

  if (loading) return <Loading />;
  if (error) return <ErrorMessage error={error} />;

  return (
    <div className="w-full max-w-6xl mx-auto">
      <h1 className="text-center text-3xl font-bold my-4">
        DRIVERS MANAGEMENT
      </h1>
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
        <FieldSearchBar
          placeholder="Search by name or surname..."
          searchTerm={searchTerm}
          setSearchTerm={setSearchTerm}
        />
        <button
          className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition"
          onClick={() => setCreateModalOpen(true)}
        >
          <Image
            className="dark:invert"
            src="/plus.svg"
            alt="Plus icon"
            width={16}
            height={16}
            priority
          />
          CREATE
        </button>
      </div>
      <DriversManagementTable
        drivers={filteredDrivers}
        onEdit={handleEdit}
        onDelete={handleDeleteClick}
      />
      <DeleteModal
        isOpen={deleteModalOpen}
        onClose={() => setDeleteModalOpen(false)}
        onConfirm={confirmDelete}
        content={{
          type: "driver",
          name: selectedDriver?.name,
          component: undefined,
        }}
      />
      <CreateModal
        isOpen={createModalOpen}
        onClose={() => setCreateModalOpen(false)}
        formData={newDriver}onChange={handleCreateInputChange}
        onSubmit={handleCreateSubmit}
      />
      <EditModal
        isOpen={editModalOpen}
        onClose={() => setEditModalOpen(false)}
        formData={editFormData}
        onChange={handleEditInputChange}
        onSubmit={handleEditSubmit}
      />
    </div>
  );
}

export default DriversManagementPage;

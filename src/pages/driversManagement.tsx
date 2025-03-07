import { useState, useEffect, useRef } from 'react';
import { GetServerSideProps, NextPage } from 'next';
import Image from 'next/image';
import { useRouter } from 'next/router';
import { getAllDrivers } from '@/controllers/driverController';
import { nationalities } from '@/controllers/utilsController';
import flagIcon from '@/components/flagIcon';

interface Driver {
  id: number;
  name: string;
  surname: string;
  nationality: string | null;
}

interface DriversPageProps {
  drivers: Driver[];
}

const DriversPage: NextPage<DriversPageProps> = ({ drivers }) => {
  const router = useRouter();
  const [deleteModalOpen, setDeleteModalOpen] = useState(false)
  const [selectedDriver, setSelectedUser] = useState<{ id: number; name: string; surname: string } | null>(null)

  const [createModalOpen, setCreateModalOpen] = useState(false)
  const [searchTerm, setSearchTerm] = useState("")
  const [filteredDrivers, setFilteredDrivers] = useState<{ id: number, name: string, surname: string, nationality: string | null }[]>([])

  const [editingDriverId, setEditingDriverId] = useState<number | null>(null)
  const [editFormData, setEditFormData] = useState({
    name: "",
    surname: "",
    nationality: "",
  })

  // Referencia para detectar clics fuera de la fila en edición
  const editRowRef = useRef<HTMLTableRowElement>(null)

  // Formulario para nuevo piloto
  const [newDriver, setNewDriver] = useState({
    name: "",
    surname: "",
    nationality: "",
  })

  useEffect(() => {
    const results = drivers.filter(
      (driver) =>
        driver.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        driver.surname.toLowerCase().includes(searchTerm.toLowerCase()),
    )
    setFilteredDrivers(results)
  }, [drivers, searchTerm])

  // Detectar clics fuera de la fila que se esta editando
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (editRowRef.current && !editRowRef.current.contains(event.target as Node)) {
        cancelEdit()
      }
    }

    // Añadir event listener solo mientras se edita
    if (editingDriverId !== null) {
      document.addEventListener("mousedown", handleClickOutside)
    }

    return () => {
      document.removeEventListener("mousedown", handleClickOutside)
    }
  }, [editingDriverId])

  const handleEdit = (user: Driver) => {
    setEditingDriverId(user.id)
    setEditFormData({
      name: user.name,
      surname: user.surname,
      nationality: user.nationality || "",
    })
  }

  const handleEditInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setEditFormData((prev) => ({ ...prev, [name]: value }))
  }

  const saveEdit = async () => {
    console.log(`Saving changes for user ID: ${editingDriverId}`, editFormData)
    try {
      const response = await fetch(`/api/driver?id=${editingDriverId}`, {
        method: "PATCH",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(editFormData),
      });
      if (response.ok) {
        console.log(`Driver with ID ${editingDriverId} deleted successfully`);
      } else {
        console.error("Error deleting driver");
      }
      router.reload();
    } catch (error) {
      console.error("Error deleting driver:", error);
    }

    // Salir del modo edición
    setEditingDriverId(null)
  }

  const cancelEdit = () => {
    setEditingDriverId(null)
  }

  const handleDeleteClick = (driver: { id: number; name: string; surname: string; }) => {
    setSelectedUser(driver)
    setDeleteModalOpen(true)
  }

  const confirmDelete = async () => {
    if (selectedDriver) {
      console.log(`Deleting driver with ID: ${selectedDriver.id}`)
      try {
        const response = await fetch(`/api/driver?id=${selectedDriver.id}`, {
          method: "DELETE",
        });
        if (response.ok) {
          console.log(`Driver with ID ${selectedDriver.id} deleted successfully`);
        } else {
          console.error("Error deleting driver");
        }
        router.reload();
      } catch (error) {
        console.error("Error deleting driver:", error);
      }

      // Cerrar modal después de eliminar
      setDeleteModalOpen(false)
      setSelectedUser(null)
    }
  }


  const handleCreateSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    console.log("Creating new driver:", newDriver)
    try {
      const response = await fetch(`/api/driver`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(newDriver),
      });
      console.log(response.status);
      if (response.ok) {
        console.log(`Driver ${newDriver.name} ${newDriver.surname} added successfully`);
        router.reload();
      } else {
        console.error("Error adding driver");
      }
    } catch (error) {
      console.error("Error adding driver:", error);
    }

    // Cerrar modal y resetear formulario
    setCreateModalOpen(false)
    setNewDriver({ name: "", surname: "", nationality: "" })
  }

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setNewDriver((prev) => ({ ...prev, [name]: value }))
  }

  return (
    <div className="w-full max-w-6xl mx-auto">
      <h1 className='text-center text-3xl font-bold my-4'>DRIVERS MANAGEMENT</h1>

      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
        <div className="relative w-full sm:w-64">
          <div className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <Image
              className="dark:invert"
              src="/search.svg"
              alt="Search"
              width={16}
              height={16}
              priority
            />
          </div>
          <input
            type="text"
            className="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
            placeholder="Search by name or surname..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
        <button
          className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition"
          onClick={() => setCreateModalOpen(true)}
        >
          <Image
            className="dark:invert"
            src="/plus.svg"
            alt="Plus"
            width={16}
            height={16}
            priority
          />
          CREATE
        </button>
      </div>

      <div className="overflow-hidden border border-gray-200 rounded-sm dark:border-gray-700">
        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead className="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th scope="col" className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                Name
              </th>
              <th scope="col" className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                Surname
              </th>
              <th scope="col" className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                Nationality
              </th>
              <th scope="col" className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                Actions
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
            {filteredDrivers.map((driver) => (
              <tr
                key={driver.id}
                ref={driver.id === editingDriverId ? editRowRef : null}
                className={`transition-all duration-200 group ${driver.id === editingDriverId
                  ? "bg-blue-50 dark:bg-blue-900/10"
                  : "bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800"
                  }`}
              >
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 relative group-hover:before:content-[''] group-hover:before:absolute group-hover:before:left-0 group-hover:before:top-0 group-hover:before:h-full group-hover:before:w-1 group-hover:before:bg-orange-500">
                  {driver.id === editingDriverId ? (
                    <input
                      type="text"
                      name="name"
                      className="w-auto p-1 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                      value={editFormData.name}
                      onChange={handleEditInputChange}
                    />
                  ) : (
                    driver.name
                  )}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {driver.id === editingDriverId ? (
                    <input
                      type="text"
                      name="surname"
                      className="w-auto p-1 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                      value={editFormData.surname}
                      onChange={handleEditInputChange}
                    />
                  ) : (
                    driver.surname
                  )}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  {driver.id === editingDriverId ? (
                    <select
                      name="nationality"
                      className="w-auto p-1 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                      value={editFormData.nationality}
                      onChange={handleEditInputChange}
                    >
                      <option value="">Select nationality</option>
                      {nationalities.map((nationality) => (
                        <option key={nationality.toLowerCase()} value={nationality}>{flagIcon(nationality.toLowerCase())} {nationality}</option>
                      ))}
                    </select>
                  ) : driver.nationality ? (
                    <div className="flex items-center">
                      <div className="flex-shrink-0 mr-2">
                        {flagIcon(driver.nationality)}
                      </div>
                      <span className="text-sm text-gray-500 dark:text-gray-400">
                        {driver.nationality}
                      </span>
                    </div>
                  ) : null}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex space-x-2">
                    {driver.id === editingDriverId ? (
                      <>
                        <button
                          className="p-2 rounded-sm border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                          onClick={saveEdit}
                        >
                          <Image
                            className="dark:invert"
                            src="/check.svg"
                            alt="Accept"
                            width={16}
                            height={16}
                            priority
                          />
                          <span className="sr-only">Confirm</span>
                        </button>
                        <button
                          className="p-2 rounded-sm border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                          onClick={cancelEdit}
                        >
                          <Image
                            className="dark:invert"
                            src="/x.svg"
                            alt="Close"
                            width={16}
                            height={16}
                            priority
                          />
                          <span className="sr-only">Cancel</span>
                        </button>
                      </>
                    ) : (
                      <>
                        <button
                          className="p-2 rounded-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                          onClick={() => handleEdit(driver)}
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
                          className="p-2 rounded-sm border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                          onClick={() => handleDeleteClick(driver)}
                        >
                          <Image
                            className="dark:invert"
                            src="/trash.svg"
                            alt="Edit"
                            width={16}
                            height={16}
                            priority
                          />
                          <span className="sr-only">Delete</span>
                        </button>
                      </>
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Modal of confirmation */}
      {deleteModalOpen && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div
              className="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
              onClick={() => setDeleteModalOpen(false)}
            ></div>
            <span className="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div className="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-sm shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <div className="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                <div className="sm:flex sm:items-start">
                  <div className="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                    <Image
                      className="w-6 h-6 text-red-600 dark:text-red-400"
                      src="/trash.svg"
                      alt="Edit"
                      width={16}
                      height={16}
                      priority
                    />
                  </div>
                  <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 className="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                      Confirm deletion
                    </h3>
                    <div className="mt-2">
                      <p className="text-sm text-gray-500 dark:text-gray-400">
                        Are you sure you want to delete the pilot {selectedDriver?.name} {selectedDriver?.surname}?
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div className="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                  type="button"
                  className="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-sm shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                  onClick={confirmDelete}
                >
                  Confirm
                </button>
                <button
                  type="button"
                  className="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-sm shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
                  onClick={() => setDeleteModalOpen(false)}
                >
                  Cancel
                </button>
              </div>
              <button
                className="absolute top-4 right-4 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                onClick={() => setDeleteModalOpen(false)}
              >
                <span className="sr-only">Close</span>
                <Image
                  className="dark:invert"
                  src="/x.svg"
                  alt="X"
                  width={24}
                  height={24}
                  priority
                />
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal */}
      {createModalOpen && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div
              className="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
              onClick={() => setCreateModalOpen(false)}
            ></div>
            <span className="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div className="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-sm shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <form onSubmit={handleCreateSubmit}>
                <div className="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6">
                  <div className="mb-4">
                    <h3 className="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                      Create new driver
                    </h3>
                    <div className="grid grid-cols-1 gap-4">
                      <div>
                        <label
                          htmlFor="name"
                          className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                          Name
                        </label>
                        <input
                          type="text"
                          name="name"
                          id="name"
                          required
                          className="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                          value={newDriver.name}
                          onChange={handleInputChange}
                        />
                      </div>
                      <div>
                        <label
                          htmlFor="surname"
                          className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                          Surname
                        </label>
                        <input
                          type="text"
                          name="surname"
                          id="surname"
                          required
                          className="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                          value={newDriver.surname}
                          onChange={handleInputChange}
                        />
                      </div>
                      <div>
                        <label
                          htmlFor="nationality"
                          className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                          Nationality
                        </label>
                        <select
                          name="nationality"
                          id="nationality"
                          className="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                          value={newDriver.nationality}
                          onChange={handleInputChange}
                        >
                          <option value="">Select nationality</option>
                          {nationalities.map((nationality) => (
                            <option key={nationality.toLowerCase()} value={nationality}>{flagIcon(nationality.toLowerCase())} {nationality}</option>
                          ))}
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button
                    type="submit"
                    className="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-sm shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                  >
                    Create
                  </button>
                  <button
                    type="button"
                    className="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-sm shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
                    onClick={() => setCreateModalOpen(false)}
                  >
                    Cancel
                  </button>
                </div>
              </form>
              <button
                className="absolute top-4 right-4 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                onClick={() => setCreateModalOpen(false)}
              >
                <span className="sr-only">Close</span>
                <Image
                  className="dark:invert"
                  src="/x.svg"
                  alt="X"
                  width={24}
                  height={24}
                  priority
                />
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export const getServerSideProps: GetServerSideProps<DriversPageProps> = async () => {
  const drivers = (await getAllDrivers()).map(driver => ({
    ...driver,
    id: Number(driver.id),
  }));
  return { props: { drivers } };
};

export default DriversPage;

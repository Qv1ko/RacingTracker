import { useState, useMemo, useRef, useEffect } from 'react';
import { GetServerSideProps, NextPage } from 'next';
import Image from 'next/image';
import { useRouter } from 'next/router';
import { getAllTeams } from '@/controllers/teamController';
import FieldSearchBar from '@/components/fieldSearchBar';
import FlagIcon from '@/components/flagIcon';
import Modal from '@/components/modal';
import { nationalities } from '@/controllers/utilsController';

interface Team {
  id: number;
  name: string;
  nationality: string | null;
}

interface TeamsManagementPageProps {
  teams: Team[];
}

// Función helper para realizar llamadas a la API de manera centralizada
// Recibe la URL, el método HTTP y, opcionalmente, el cuerpo de la petición
const API_CALL = async (url: string, method: string, body?: unknown) => {
  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: body ? JSON.stringify(body) : null,
  });
  return response.ok;
};

// Componente principal de la página de gestión de teams
const TeamsManagementPage: NextPage<TeamsManagementPageProps> = ({ teams }) => {
  // Hook para acceder a las rutas y recargar la página
  const router = useRouter();

  // Estados para controlar la visibilidad de modales y la selección de team
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [selectedTeam, setSelectedTeam] = useState<Team | null>(null);
  const [createModalOpen, setCreateModalOpen] = useState(false);

  // Estado para el término de búsqueda ingresado en el campo de búsqueda
  const [searchTerm, setSearchTerm] = useState('');

  // Estado para controlar qué team se está editando y sus datos en edición
  const [editingTeamId, setEditingTeamId] = useState<number | null>(null);
  const [editFormData, setEditFormData] = useState({ name: '', nationality: '' });

  // Referencia para detectar clics fuera de la fila que se está editando
  const editRowRef = useRef<HTMLTableRowElement>(null);

  // Estado para el formulario de creación de un nuevo team
  const [newTeam, setNewTeam] = useState({ name: '', nationality: '' });

  // useMemo para optimizar el filtrado de teams según el término de búsqueda
  const filteredTeams = useMemo(() => {
    return teams.filter((team) => team.name.toLowerCase().includes(searchTerm.toLowerCase()));
  }, [teams, searchTerm]);

  // useEffect para detectar clics fuera de la fila en edición y cancelar la edición
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (editRowRef.current && !editRowRef.current.contains(event.target as Node)) {
        cancelEdit();
      }
    };
    // Si se está editando, se añade el listener para detectar clics externos
    if (editingTeamId !== null) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    // Se elimina el listener al desmontar o cambiar el estado de edición
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [editingTeamId]);

  // Función para iniciar el modo edición y cargar los datos del team a editar
  const handleEdit = (team: Team) => {
    setEditingTeamId(team.id);
    setEditFormData({
      name: team.name,
      nationality: team.nationality || '',
    });
  };

  // Función para manejar los cambios en los inputs del formulario de edición
  const handleEditInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setEditFormData((prev) => ({ ...prev, [name]: value }));
  };

  // Función para guardar los cambios del team editado
  const saveEdit = async () => {
    if (!editingTeamId) return;
    // Se realiza la llamada a la API para actualizar el team
    const success = await API_CALL(`/api/team?id=${editingTeamId}`, 'PATCH', editFormData);
    if (success) console.log(`Team ${editingTeamId} updated successfully`);
    else console.error('Error updating team');
    // Se cierra el modo edición y se recarga la página
    setEditingTeamId(null);
    router.reload();
  };

  // Función para cancelar la edición
  const cancelEdit = () => setEditingTeamId(null);

  // Función para abrir el modal de eliminación y seleccionar el team correspondiente
  const handleDeleteClick = (team: Team) => {
    setSelectedTeam(team);
    setDeleteModalOpen(true);
  };

  // Función para confirmar la eliminación del team seleccionado
  const confirmDelete = async () => {
    if (selectedTeam) {
      // Llamada a la API para eliminar el team
      const success = await API_CALL(`/api/team?id=${selectedTeam.id}`, 'DELETE');
      if (success) console.log(`Team ${selectedTeam.id} deleted successfully`);
      else console.error('Error deleting team');
      // Se cierra el modal y se reinicia la selección
      setDeleteModalOpen(false);
      setSelectedTeam(null);
      router.reload();
    }
  };

  // Función para manejar el envío del formulario de creación de un nuevo team
  const handleCreateSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    // Se realiza la llamada a la API para crear el team
    const success = await API_CALL(`/api/team`, 'POST', newTeam);
    if (success) console.log(`Team ${newTeam.name} added successfully`);
    else console.error('Error adding team');
    // Se cierra el modal, se resetean los campos y se recarga la página
    setCreateModalOpen(false);
    setNewTeam({ name: '', nationality: '' });
    router.reload();
  };

  // Función para manejar los cambios en los inputs del formulario de creación
  const handleNewTeamChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setNewTeam((prev) => ({ ...prev, [name]: value }));
  };

  return (
    <div className="w-full max-w-6xl mx-auto">
      <h1 className="text-center text-3xl font-bold my-4">TEAMS MANAGEMENT</h1>

      {/* Sección de búsqueda y creación */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
        {/* Componente de búsqueda */}
        <FieldSearchBar
          placeholder="Search by name..."
          searchTerm={searchTerm}
          setSearchTerm={setSearchTerm}
        />
        {/* Botón adicional para abrir el modal de creación */}
        <button
          className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-sm font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition"
          onClick={() => setCreateModalOpen(true)}
        >
          <Image className="dark:invert" src="/plus.svg" alt="Plus icon" width={16} height={16} priority />
          CREATE
        </button>
      </div>

      {/* Tabla que muestra la lista de teams */}
      <div className="overflow-hidden border border-gray-200 rounded-sm dark:border-gray-700">
        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead className="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Name</th>
              <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Nationality</th>
              <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
            {/* Iteramos sobre los teams filtrados */}
            {filteredTeams.map((team) => (
              <tr
                key={team.id}
                // Asignamos la referencia para detectar clics externos solo en la fila en edición
                ref={team.id === editingTeamId ? editRowRef : null}
                className={`transition-all duration-200 group ${
                  team.id === editingTeamId
                    ? 'bg-blue-50 dark:bg-blue-900/10'
                    : 'bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800'
                }`}
              >
                {/* Columna de nombre: si está en edición se muestra un input, sino el valor */}
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 relative group-hover:before:content-[''] group-hover:before:absolute group-hover:before:left-0 group-hover:before:top-0 group-hover:before:h-full group-hover:before:w-1 group-hover:before:bg-orange-500">
                  {team.id === editingTeamId ? (
                    <input
                      type="text"
                      name="name"
                      className="w-auto p-1 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                      value={editFormData.name}
                      onChange={handleEditInputChange}
                    />
                  ) : (
                    team.name
                  )}
                </td>
                {/* Columna de nacionalidad */}
                <td className="px-6 py-4 whitespace-nowrap">
                  {team.id === editingTeamId ? (
                    <select
                      name="nationality"
                      className="w-auto p-1 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                      value={editFormData.nationality}
                      onChange={handleEditInputChange}
                    >
                      <option value="">Select nationality</option>
                      {nationalities.map((n) => (
                        <option key={n.toLowerCase()} value={n}>
                          {FlagIcon(n.toLowerCase())} {n}
                        </option>
                      ))}
                    </select>
                  ) : team.nationality ? (
                    <div className="flex items-center">
                      <div className="flex-shrink-0 mr-2">{FlagIcon(team.nationality)}</div>
                      <span className="text-sm text-gray-500 dark:text-gray-400">{team.nationality}</span>
                    </div>
                  ) : null}
                </td>
                {/* Columna de acciones (editar, eliminar) */}
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex space-x-2">
                    {team.id === editingTeamId ? (
                      <>
                        {/* Botón para confirmar la edición */}
                        <button
                          className="p-2 rounded-sm border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                          onClick={saveEdit}
                        >
                          <Image className="dark:invert" src="/check.svg" alt="Confirm" width={16} height={16} priority />
                          <span className="sr-only">Confirm</span>
                        </button>
                        {/* Botón para cancelar la edición */}
                        <button
                          className="p-2 rounded-sm border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                          onClick={cancelEdit}
                        >
                          <Image className="dark:invert" src="/x.svg" alt="Cancel" width={16} height={16} priority />
                          <span className="sr-only">Cancel</span>
                        </button>
                      </>
                    ) : (
                      <>
                        {/* Botón para iniciar la edición */}
                        <button
                          className="p-2 rounded-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                          onClick={() => handleEdit(team)}
                        >
                          <Image className="dark:invert" src="/edit.svg" alt="Edit" width={16} height={16} priority />
                          <span className="sr-only">Edit</span>
                        </button>
                        {/* Botón para eliminar el team */}
                        <button
                          className="p-2 rounded-sm border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                          onClick={() => handleDeleteClick(team)}
                        >
                          <Image className="dark:invert" src="/trash.svg" alt="Delete" width={16} height={16} priority />
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

      {/* Modal de confirmación para eliminar un team */}
      {deleteModalOpen && (
        <Modal onClose={() => setDeleteModalOpen(false)}>
          <div className="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
            <div className="sm:flex sm:items-start">
              {/* Icono de advertencia */}
              <div className="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                <Image className="w-6 h-6" src="/trash.svg" alt="Delete" width={16} height={16} priority />
              </div>
              {/* Texto de confirmación */}
              <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 className="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                  Confirm deletion
                </h3>
                <div className="mt-2">
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    Are you sure you want to delete team {selectedTeam?.name}
                  </p>
                </div>
              </div>
            </div>
          </div>
          {/* Botones de acción en el modal de eliminación */}
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
        </Modal>
      )}

      {/* Modal para crear un nuevo team */}
      {createModalOpen && (
        <Modal onClose={() => setCreateModalOpen(false)}>
          <form onSubmit={handleCreateSubmit}>
            <div className="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6">
              <div className="mb-4">
                <h3 className="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                  Create new team
                </h3>
                <div className="grid grid-cols-1 gap-4">
                  {/* Campo para ingresar el nombre */}
                  <div>
                    <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Name
                    </label>
                    <input
                      type="text"
                      name="name"
                      id="name"
                      required
                      className="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                      value={newTeam.name}
                      onChange={handleNewTeamChange}
                    />
                  </div>
                  {/* Campo para seleccionar la nacionalidad */}
                  <div>
                    <label htmlFor="nationality" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Nationality
                    </label>
                    <select
                      name="nationality"
                      id="nationality"
                      className="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                      value={newTeam.nationality}
                      onChange={handleNewTeamChange}
                    >
                      <option value="">Select nationality</option>
                      {nationalities.map((n) => (
                        <option key={n.toLowerCase()} value={n}>
                          {FlagIcon(n.toLowerCase())} {n}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>
              </div>
            </div>
            {/* Botones de acción para el modal de creación */}
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
        </Modal>
      )}
    </div>
  );
};

export const getServerSideProps: GetServerSideProps<TeamsManagementPageProps> = async () => {
  const teams = (await getAllTeams()).map(team => ({
    ...team,
    id: Number(team.id),
  }));
  return { props: { teams } };
};

export default TeamsManagementPage;

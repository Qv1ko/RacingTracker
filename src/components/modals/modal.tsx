import Image from 'next/image';

// Modal props
interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  children: React.ReactNode;
}

const Modal: React.FC<ModalProps> = ({ isOpen, onClose, children }) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto">
      <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {/* Fondo semitransparente que cierra el modal al hacer clic */}
        <div className="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onClick={onClose}></div>
        {/* Elemento invisible para centrar el modal en pantallas pequeñas */}
        <span className="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        {/* Contenedor principal del modal */}
        <div className="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-sm shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          {children}
          {/* Botón para cerrar el modal */}
          <button
            className="absolute top-4 right-4 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
            onClick={onClose}
          >
            <span className="sr-only">Close</span>
            <Image className="dark:invert" src="/x.svg" alt="Close" width={24} height={24} priority />
          </button>
        </div>
      </div>
    </div>
  );
};

export default Modal;

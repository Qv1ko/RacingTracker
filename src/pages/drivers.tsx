import { GetServerSideProps, NextPage } from 'next';
import { getAllDrivers } from '@/controllers/driverController';
import FlagIcon from "@/components/flagIcon";

interface DriversPageProps {
  drivers: {
    id: string;
    name: string;
    surname: string;
    nationality: string | null;
  }[];
}

const DriversPage: NextPage<DriversPageProps> = ({ drivers }) => {
  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-4">Pilotos</h1>
      {drivers.length === 0 ? (
        <p>No hay pilotos.</p>
      ) : (
        <ul>
          {drivers.map((driver) => (
            <li key={driver.id} className="border p-4 mb-2 rounded">
              <p className="text-gray-800">{driver.name}</p>
              <p className="text-xs text-gray-400">
                {driver.surname}
              </p>
              <p className="text-xs text-gray-400">
              {driver.nationality && (
                <>{FlagIcon(driver.nationality)} {driver.nationality}</>
              )}
              </p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export const getServerSideProps: GetServerSideProps<DriversPageProps> = async () => {
  const drivers = (await getAllDrivers()).map(driver => ({
    ...driver,
    id: driver.id.toString(),
  }));
  return { props: { drivers } };
};

export default DriversPage;

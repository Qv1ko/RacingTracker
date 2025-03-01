import { GetServerSideProps, NextPage } from 'next';
import { getAllTeams } from '@/controllers/teamController';
import flagIcon from '@/components/flagIcon';

interface TeamsPageProps {
  teams: {
    id: string;
    name: string;
    nationality: string | null;
  }[];
}

const TeamsPage: NextPage<TeamsPageProps> = ({ teams }) => {
  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-4">Pilotos</h1>
      {teams.length === 0 ? (
        <p>No hay pilotos.</p>
      ) : (
        <ul>
          {teams.map((team) => (
            <li key={team.id} className="border p-4 mb-2 rounded">
              <p className="text-gray-800">{team.name}</p>
              <p className="text-xs text-gray-400">
                {team.nationality && (
                  <>{flagIcon(team.nationality)} {team.nationality}</>
                )}
              </p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export const getServerSideProps: GetServerSideProps<TeamsPageProps> = async () => {
  const teams = (await getAllTeams()).map(team => ({
    ...team,
    id: team.id.toString(),
  }));
  return { props: { teams } };
};

export default TeamsPage;

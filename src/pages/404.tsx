import Link from "next/link"

function NotFound() {
  return (
    <div className="min-h-screen w-full flex flex-col items-center justify-center p-6 relative overflow-hidden">
      <div className="z-10 flex flex-col items-center text-center max-w-2xl">

        <h1 className="text-9xl font-light text-white mb-6 tracking-tighter">
          <span className="text-orange-500 font-medium">4</span>
          <span className="text-zinc-400">0</span>
          <span className="text-orange-500 font-medium">4</span>
        </h1>

        <p className="text-2xl text-zinc-300 font-light mb-8 tracking-wide">Page not found</p>

        <p className="text-zinc-400 max-w-md mb-12 italic">Looks like you&apos;ve gone off track and into the gravel.</p>

        <Link
          href="/"
          className="px-8 py-3 bg-transparent border border-orange-500 text-orange-500 hover:bg-orange-500/10 transition-colors duration-300 text-sm uppercase tracking-wider font-light"
        >
          Return to home page
        </Link>
      </div>
    </div>
  )
}

export default NotFound;

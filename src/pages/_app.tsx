import Footer from '@/components/footer';
import Header from '@/components/header';
import "@/styles/globals.css";
import type { AppProps } from "next/app";
import { Roboto } from 'next/font/google';

const roboto = Roboto({
  subsets: ['latin'],
})

function App({ Component, pageProps }: AppProps) {
  return (
    <>
      <Header />
      <main className={roboto.className}>
        <Component {...pageProps} />
      </main>
      <Footer />
    </>
  );
}

export default App;

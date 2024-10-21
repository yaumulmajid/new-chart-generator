import { GoDownload } from "react-icons/go";
import { axiosInstance } from "../lib/axios";
import { useEffect, useState } from "react";
import { IProduct } from "../lib/type";
import { Bounce, toast, ToastContainer } from "react-toastify";
import DocumentGenerate from "../components/document/DocumentGenerate";

const Home = () => {
  const [products, productsSet] = useState<IProduct[]>([]);
  const [filteredProducts, filteredProductsSet] = useState<IProduct[]>([]); // State untuk hasil pencarian
  const [isLoading, isLoadingSet] = useState<boolean>(false);
  const [isError, isErrorSet] = useState<boolean>(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const searchQuery = e.target.value.toLowerCase();

    const filtered = products.filter(product =>
      product.title.toLowerCase().includes(searchQuery)
    );

    filteredProductsSet(filtered);
  };

  const handleGenerate = () => {
    console.log(filteredProducts);
  };

  const fetchProduct = async () => {
    isLoadingSet(true);
    try {
      const res = await axiosInstance.get('/');
      productsSet(res.data);
      filteredProductsSet(res.data);
    } catch (error: unknown) {
      toast.error('An unexpected error occurred', {
        position: 'top-center',
      });
      isErrorSet(true);
    } finally {
      isLoadingSet(false);
    }
  };

  useEffect(() => {
    fetchProduct();
  }, []);

  return (
    <div className="container mx-auto max-w-[960px] py-10 px-5 space-y-14">
      {/* logo */}
      <div className="flex justify-center">
        <div className="bg-blue-400 px-8 py-4 w-56 rounded">
          <p className="text-center text-3xl text-white">logo</p>
        </div>
      </div>

     <DocumentGenerate />

      {/* title */}
      <h1 className="text-center font-bold text-2xl">Chart Generator</h1>

      {/* list products */}
      <div className="text-neutral-500">
        <p>Site Name</p>
        <input
          type="text"
          name=""
          className="border-2 outline-blue-500 p-2 rounded"
        />
      </div>

      <div className="space-y-5">
        <div className="text-neutral-500">
          <p>Select Products</p>
          <input
            type="text"
            name=""
            className="border-2 outline-blue-400 p-2 rounded"
            placeholder="Type product name to search"
            onChange={handleChange} // Panggil handleChange saat input berubah
          />
        </div>

        <button
          className="flex items-center gap-x-2 text-white bg-blue-500 font-semibold py-2 px-6 rounded-sm"
          onClick={handleGenerate}
        >
          <GoDownload className="text-lg" />
          Generate Chart
        </button>

        {isLoading ? (
          <div className="flex items-center justify-center">
            <svg
              className="animate-spin h-5 w-5 mr-3 border-t-2 border-blue-500 rounded-full"
              viewBox="0 0 24 24"
            />
            <span className="text-neutral-700 animate-pulse text-xl">
              loading...
            </span>
          </div>
        ) : isError ? (
          <p className="text-red-500 font-bold text-2xl text-center">
            Oops Something Went Wrong
          </p>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            {filteredProducts.map((e, i) => (
              <div className="flex gap-x-2 items-center" key={i}>
                <input
                  type="checkbox"
                  name=""
                  className="border border-neutral-50 cursor-pointer"
                />
                <p className="text-neutral-800">{e.title}</p>
              </div>
            ))}
          </div>
        )}
      </div>

      <ToastContainer
        position="top-center"
        autoClose={5000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="light"
        transition={Bounce}
      />
    </div>
  );
};

export default Home;

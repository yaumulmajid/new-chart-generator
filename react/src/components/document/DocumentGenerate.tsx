import { Document, Page, PDFViewer, StyleSheet, Text, View } from "@react-pdf/renderer";
import { useState } from "react";

const styles = StyleSheet.create({
    viewer: {
        width: "100%", // window.innerWidth,
        height: "100vh", // window.innerHeight
    },
    page: {
        backgroundColor: "#d11fb6",
        color: "white"
    }
})

const DocumentGenerate = () => {
    const [coba, cobaSet] = useState('hallow')

    return (
        <PDFViewer style={styles.viewer}>
            <Document>
                <Page size={'A4'} style={styles.page}>
                    {
                        [...Array(5)].map((_, i) => (
                            <View key={i}>
                                <Text>{coba}</Text>
                            </View>
                        ))
                    }
                    <View>
                        <Text>Lorem ipsum dolor sit amet consectetur adipisicing elit. Culpa unde veritatis rem voluptate laudantium. Quod nihil repellat quis, maiores tempore iste quisquam! Doloribus ex iure qui eveniet similique fugit vel.</Text>
                    </View>
                    <View>
                        <Text>Lorem ipsum dolor sit amet consectetur adipisicing elit. Unde, tenetur! Aliquid quo, maxime perferendis eos accusantium exercitationem. Animi corrupti eligendi provident repellendus explicabo hic, nemo nihil repudiandae! Vitae, possimus commodi!</Text>
                    </View>
                </Page>
            </Document>
        </PDFViewer>
    )
}

export default DocumentGenerate

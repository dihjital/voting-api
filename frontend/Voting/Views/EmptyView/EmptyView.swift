//
//  EmptyView.swift
//  Voting
//
//  Created by Szabolcs Tóth on 24.11.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import SwiftUI

struct EmptyView: View {
    var body: some View {
        content
    }
    
    @ViewBuilder var content: some View {
        VStack {
            Text("🤷‍♂️")
                .font(.largeTitle)
        }
    }
}

struct EmptyView_Previews: PreviewProvider {
    static var previews: some View {
        EmptyView()
    }
}
